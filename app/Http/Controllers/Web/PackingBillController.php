<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PackingBill;
use App\Models\PackingMaterial;
use App\Models\StagingOut;
use App\Models\StagingOutHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PackingBillController extends Controller
{
    /**
     * Halaman bill sebuah SO (buat baru / edit).
     *
     * Struktur bill: SO -> koli (kardus) -> material. Tiap koli punya "isi koli"
     * (item di dalamnya). Material bersama semua koli (pallet, wrapping) disimpan
     * terpisah dan hanya dipakai kalau koli lebih dari 1.
     */
    public function form(Request $request)
    {
        $request->validate(['so_number' => ['required', 'string', 'max:255']]);

        $so = trim($request->so_number);

        $rows = StagingOut::with('item')->where('so_number', $so)->orderBy('id')->get();
        // Item SO ini yang sudah terkirim (barisnya sudah pindah ke history).
        // Tetap ditampilkan supaya bill SO yang sudah dikirim bisa diedit penuh.
        $archived = $this->archivedRows($so, $rows->pluck('id')->all());
        $bill = $this->loadBill($so);

        if ($rows->isEmpty() && $archived->isEmpty() && ! $bill) {
            abort(404, 'SO tidak ditemukan.');
        }

        $customer = $rows->pluck('customer')->filter()->unique()->implode(', ')
            ?: ($bill->customer ?? '')
            ?: $archived->pluck('customer')->filter()->unique()->implode(', ');

        $payload = [
            'so_number' => $so,
            'customer' => $customer,
            'items' => $rows->toBase()->map(fn ($r) => [
                'id' => $r->id,
                'label' => $this->itemLabel($r),
                'qty' => $r->qty,
            ])->concat($archived->toBase()->map(fn ($h) => [
                'id' => $h->staging_out_id,
                'label' => $this->historyItemLabel($h),
                'qty' => $h->initial_qty,
            ]))->values(),
            'materials' => PackingMaterial::where('aktif', true)->orderBy('nama')
                ->get(['id', 'kode', 'nama', 'satuan', 'harga']),
            'bill' => $bill ? [
                'id' => $bill->id,
                'total' => $bill->total,
                'kolis' => $bill->kolis->map(fn ($k) => [
                    'id' => $k->id,
                    'items' => $k->items->map(fn ($i) => [
                        'staging_out_id' => $i->staging_out_id,
                        'item_label' => $i->item_label,
                    ])->values(),
                    'lines' => $k->lines->map(fn ($l) => $this->linePayload($l))->values(),
                ])->values(),
                'shared' => $bill->sharedLines->map(fn ($l) => $this->linePayload($l))->values(),
            ] : null,
        ];

        // Dibuka dari halaman Bill History -> "Kembali" ke sana, bukan ke Staging Out.
        if ($request->query('from') === 'history' && \Illuminate\Support\Facades\Route::has('packing-bills.index')) {
            $backUrl = route('packing-bills.index');
        } else {
            $backUrl = \Illuminate\Support\Facades\Route::has('stagings-out.index')
                ? route('stagings-out.index')
                : url()->previous();
        }

        return view('packing_bill_form', [
            'payload' => $payload,
            'backUrl' => $backUrl,
        ]);
    }

    /**
     * Simpan bill (buat baru atau edit). 1 SO = 1 bill.
     *
     * Aturan harga: baris lama yang materialnya tidak diganti tetap memakai
     * harga saat pertama disimpan; baris baru / material yang diganti memakai
     * harga master saat ini.
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'so_number' => ['required', 'string', 'max:255'],
            'kolis' => ['present', 'array', 'max:50'],
            'kolis.*.id' => ['nullable', 'integer'],
            'kolis.*.items' => ['nullable', 'array'],
            'kolis.*.items.*' => ['integer'],
            'kolis.*.lines' => ['present', 'array'],
            'kolis.*.lines.*.id' => ['nullable', 'integer'],
            'kolis.*.lines.*.material_id' => ['required', 'integer', 'exists:packing_materials,id'],
            'kolis.*.lines.*.qty' => ['required', 'numeric', 'gt:0', 'max:999999'],
            'shared' => ['nullable', 'array'],
            'shared.*.id' => ['nullable', 'integer'],
            'shared.*.material_id' => ['required', 'integer', 'exists:packing_materials,id'],
            'shared.*.qty' => ['required', 'numeric', 'gt:0', 'max:999999'],
        ], [
            'kolis.*.lines.*.material_id.required' => 'Material wajib dipilih.',
            'kolis.*.lines.*.material_id.exists' => 'Material tidak ditemukan.',
            'kolis.*.lines.*.qty.required' => 'Qty wajib diisi.',
            'kolis.*.lines.*.qty.numeric' => 'Qty harus berupa angka.',
            'kolis.*.lines.*.qty.gt' => 'Qty harus lebih dari 0.',
            'shared.*.material_id.required' => 'Material wajib dipilih.',
            'shared.*.material_id.exists' => 'Material tidak ditemukan.',
            'shared.*.qty.required' => 'Qty wajib diisi.',
            'shared.*.qty.numeric' => 'Qty harus berupa angka.',
            'shared.*.qty.gt' => 'Qty harus lebih dari 0.',
        ]);

        $so = trim($data['so_number']);
        $user = $this->currentUser($request);
        $kolis = array_values($data['kolis']);
        $shared = array_values($data['shared'] ?? []);
        $koliCount = count($kolis);

        // Aturan koli
        if ($koliCount > 1) {
            foreach ($kolis as $i => $k) {
                if (empty($k['items'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Koli '.($i + 1).': pilih isi koli (minimal 1 item).',
                    ], 422);
                }
            }
        } elseif (! empty($shared)) {
            return response()->json([
                'success' => false,
                'message' => 'Material bersama hanya dipakai jika koli lebih dari 1.',
            ], 422);
        }

        $totalLines = count($shared) + collect($kolis)->sum(fn ($k) => count($k['lines']));

        try {
            $result = DB::transaction(function () use ($so, $user, $kolis, $shared, $koliCount, $totalLines) {
                $rows = StagingOut::with('item')->where('so_number', $so)->get()->keyBy('id');
                $archived = $this->archivedRows($so, $rows->keys()->all())->keyBy('staging_out_id');
                $bill = PackingBill::where('so_number', $so)->lockForUpdate()->first();

                if ($rows->isEmpty() && $archived->isEmpty() && ! $bill) {
                    abort(422, 'SO tidak ditemukan.');
                }

                // Semua material dihapus user: bill dianggap tidak ada. Bill lama dihapus
                // (SO kembali ke status "belum ada bill"), bill baru tidak dibuat.
                if ($totalLines === 0) {
                    if ($bill) {
                        $bill->delete();

                        return ['bill' => null, 'deleted' => true];
                    }

                    return ['bill' => null, 'deleted' => false];
                }

                if (! $bill) {
                    $bill = PackingBill::create([
                        'so_number' => $so,
                        'customer' => $rows->pluck('customer')->filter()->unique()->implode(', ')
                            ?: $archived->pluck('customer')->filter()->unique()->implode(', ')
                            ?: null,
                        'tanggal' => now()->toDateString(),
                        'total' => 0,
                        'dibuat_oleh' => $user,
                    ]);
                } else {
                    $bill->diubah_oleh = $user;
                }

                $oldKolis = $bill->kolis()->with('items')->get()->keyBy('id');
                $oldLines = $bill->lines()->get()->keyBy('id');

                $materialIds = collect($kolis)->flatMap(fn ($k) => collect($k['lines'])->pluck('material_id'))
                    ->merge(collect($shared)->pluck('material_id'))->unique();
                $materials = PackingMaterial::whereIn('id', $materialIds)->get()->keyBy('id');

                $keepKoli = [];
                $keepLines = [];
                $total = 0;

                // Simpan 1 baris material (baru / update), kembalikan id & harga tersimpan.
                $saveLine = function (array $in, ?int $koliId, int $sort) use (&$keepLines, &$total, $oldLines, $materials, $bill) {
                    $old = ! empty($in['id']) ? $oldLines->get((int) $in['id']) : null;
                    $mat = $materials->get((int) $in['material_id']);
                    $sameMaterial = $old && (int) $old->packing_material_id === (int) $mat->id;

                    if (! $sameMaterial && ! $mat->aktif) {
                        abort(422, 'Material "'.$mat->nama.'" sudah nonaktif.');
                    }

                    $qty = round((float) $in['qty'], 3);
                    $harga = $sameMaterial ? (float) $old->harga : (float) $mat->harga;
                    $subtotal = round($qty * $harga, 2);

                    $payload = [
                        'packing_koli_id' => $koliId,
                        'packing_material_id' => $mat->id,
                        'nama_material' => $sameMaterial ? $old->nama_material : $mat->nama,
                        'satuan' => $sameMaterial ? $old->satuan : $mat->satuan,
                        'qty' => $qty,
                        'harga' => $harga,
                        'subtotal' => $subtotal,
                        'sort_order' => $sort,
                    ];

                    if ($old) {
                        $old->update($payload);
                        $line = $old;
                    } else {
                        $line = $bill->lines()->create($payload);
                    }

                    $keepLines[] = $line->id;
                    $total += $subtotal;

                    return [
                        'id' => $line->id,
                        'packing_material_id' => $mat->id,
                        'satuan' => $payload['satuan'],
                        'harga' => $harga,
                    ];
                };

                $savedKolis = [];

                foreach ($kolis as $i => $k) {
                    $old = ! empty($k['id']) ? $oldKolis->get((int) $k['id']) : null;

                    if ($old) {
                        $old->update(['no_koli' => $i + 1]);
                        $koli = $old;
                    } else {
                        $koli = $bill->kolis()->create(['no_koli' => $i + 1]);
                    }
                    $keepKoli[] = $koli->id;

                    // Isi koli. Kalau SO hanya 1 koli, isinya otomatis semua item (tidak dicatat).
                    $oldLabels = $old ? $old->items->pluck('item_label', 'staging_out_id') : collect();
                    $koli->items()->delete();

                    if ($koliCount > 1) {
                        foreach (array_unique(array_map('intval', $k['items'] ?? [])) as $sid) {
                            if ($rows->has($sid)) {
                                $label = $this->itemLabel($rows->get($sid));
                            } elseif ($archived->has($sid)) {
                                $label = $this->historyItemLabel($archived->get($sid)); // sudah terkirim
                            } elseif ($oldLabels->has($sid)) {
                                $label = $oldLabels->get($sid); // barisnya sudah pindah ke history
                            } else {
                                abort(422, 'Item tidak ada di SO ini.');
                            }

                            $koli->items()->create(['staging_out_id' => $sid, 'item_label' => $label]);
                        }
                    }

                    $lineOut = [];
                    foreach (array_values($k['lines']) as $j => $in) {
                        $lineOut[] = $saveLine($in, $koli->id, $j);
                    }

                    $savedKolis[] = ['id' => $koli->id, 'lines' => $lineOut];
                }

                $savedShared = [];
                foreach ($shared as $j => $in) {
                    $savedShared[] = $saveLine($in, null, $j);
                }

                // Baris & koli yang dihapus user di form
                \App\Models\PackingBillLine::where('packing_bill_id', $bill->id)->whereNotIn('id', $keepLines)->delete();
                \App\Models\PackingKoli::where('packing_bill_id', $bill->id)->whereNotIn('id', $keepKoli)->delete();

                $bill->total = round($total, 2);
                $bill->save();

                return [
                    'bill' => $bill,
                    'deleted' => false,
                    'isNew' => $bill->wasRecentlyCreated,
                    'kolis' => $savedKolis,
                    'shared' => $savedShared,
                ];
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }

        if ($result['bill'] === null) {
            return response()->json([
                'success' => true,
                'deleted' => true,
                'message' => $result['deleted']
                    ? 'Semua material dihapus, jadi bill ini dihapus.'
                    : 'Tidak ada material, bill tidak dibuat.',
                'bill_id' => null,
                'total' => 0,
                'kolis' => [],
                'shared' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $result['isNew'] ? 'Bill berhasil dibuat.' : 'Bill berhasil diperbarui.',
            'bill_id' => $result['bill']->id,
            'total' => $result['bill']->total,
            // id & harga tersimpan tiap baris, supaya halaman bisa langsung diperbarui tanpa reload
            'kolis' => $result['kolis'],
            'shared' => $result['shared'],
        ]);
    }

    /**
     * Halaman bill yang rapi untuk dicetak / disimpan PDF (diberikan ke customer).
     */
    public function print(PackingBill $packingBill)
    {
        $bill = $this->loadBill($packingBill->so_number);

        return view('packing_bill_print', ['bill' => $bill]);
    }

    protected function loadBill(string $so): ?PackingBill
    {
        return PackingBill::with([
            'kolis' => fn ($q) => $q->orderBy('no_koli'),
            'kolis.items',
            'kolis.lines' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
            'sharedLines' => fn ($q) => $q->orderBy('sort_order')->orderBy('id'),
        ])->where('so_number', $so)->first();
    }

    protected function linePayload($l): array
    {
        return [
            'id' => $l->id,
            'packing_material_id' => $l->packing_material_id,
            'nama_material' => $l->nama_material,
            'satuan' => $l->satuan,
            'qty' => $l->qty,
            'harga' => $l->harga,
        ];
    }

    /**
     * Baris SO ini yang sudah terkirim dan dipindah ke history (barisnya di
     * staging_outs sudah tidak ada). $exceptIds = id baris yang masih ada.
     */
    protected function archivedRows(string $so, array $exceptIds = [])
    {
        return StagingOutHistory::with('item')
            ->where('so_number', $so)
            ->whereNotNull('delivery_date')
            ->whereNotNull('staging_out_id')
            ->when(count($exceptIds), fn ($q) => $q->whereNotIn('staging_out_id', $exceptIds))
            ->orderBy('id')
            ->get();
    }

    protected function historyItemLabel(StagingOutHistory $history): string
    {
        return $history->item ? (string) $history->item->item_code_internal : 'Barang #'.$history->staging_out_id;
    }

    /** Label item di bill: kode item saja (tanpa nama dan line item). */
    protected function itemLabel(StagingOut $row): string
    {
        return $row->item ? (string) $row->item->item_code_internal : 'Barang #'.$row->id;
    }

    /**
     * Pengguna yang membuat/mengubah bill. Aplikasi diakses lewat Cloudflare
     * Access, jadi emailnya dibaca dari header bawaan Cloudflare.
     */
    protected function currentUser(Request $request): string
    {
        return $request->header('Cf-Access-Authenticated-User-Email') ?: 'System';
    }
}