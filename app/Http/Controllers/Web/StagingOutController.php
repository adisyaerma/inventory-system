<?php

namespace App\Http\Controllers\Web;

use App\Exports\StagingOutExport;
use App\Exports\StagingOutTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\StagingOutImport;
use App\Imports\StagingOutStockImport;
use App\Models\Item;
use App\Models\Location;
use App\Models\LocationStock;
use App\Models\StagingIn;
use App\Models\PackingBill;
use App\Models\StagingOut;
use App\Models\StagingOutHistory;
use App\Models\StockMutation;
use App\Services\StagingInHistoryService;
use App\Services\StagingOutHistoryService;
use App\Services\StagingInReturnService;
use App\Services\StagingOutStockService;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class StagingOutController extends Controller
{
    protected array $groupCache = [];

    protected ?array $locationNameCache = null;

    public function __construct(protected StagingOutHistoryService $history)
    {
    }

    protected function groupOf(StagingOut $row, bool $lock = false): Collection
    {
        if ($row->so_number === null || $row->so_number === '' || ! $row->item_id) {
            return collect([$row]);
        }

        $query = StagingOut::query()
            ->where('so_number', $row->so_number)
            ->where('item_id', $row->item_id)
            ->whereRaw("COALESCE(line_item, '') = ?", [(string) $row->line_item])
            ->whereRaw("COALESCE(customer, '') = ?", [(string) $row->customer])
            ->orderBy('id');

        if ($lock) {
            $query->lockForUpdate();
        }

        $rows = $query->get();

        return $rows->isEmpty() ? collect([$row]) : $rows;
    }

    protected function groupFor(StagingOut $row): Collection
    {
        return $this->groupCache[$row->id] ??= $this->groupOf($row);
    }

    protected function expandGroups(Collection $rows): Collection
    {
        return $rows
            ->flatMap(fn (StagingOut $row) => $this->groupOf($row))
            ->unique('id')
            ->values();
    }

    protected function onlyGroupRepresentatives($query)
    {
        return $query->whereNotExists(function ($sub) {
            $sub->select(DB::raw(1))
                ->from('staging_outs as sib')
                ->whereColumn('sib.so_number', 'staging_outs.so_number')
                ->whereColumn('sib.item_id', 'staging_outs.item_id')
                ->whereRaw("COALESCE(sib.line_item, '') = COALESCE(staging_outs.line_item, '')")
                ->whereRaw("COALESCE(sib.customer, '') = COALESCE(staging_outs.customer, '')")
                ->whereRaw('sib.id < staging_outs.id')
                ->where('sib.so_number', '!=', '');
        });
    }

    protected function locationNames(): array
    {
        return $this->locationNameCache ??= Location::pluck('location_name', 'id')->all();
    }

    protected function groupSourceLines(Collection $group): string
    {
        return $group->map(function (StagingOut $r) {
            if ($r->source_type === 'stock' && $r->location_id) {
                $badge = '<span class="badge bg-info-subtle text-info">Stok ('.(int) $r->qty.')</span>';
            } elseif ($r->staging_in_id) {
                $badge = '<span class="badge bg-primary-subtle text-primary">Staging In ('.(int) $r->qty.')</span>';
            } else {
                $badge = '<span class="badge bg-warning-subtle text-warning">Sumber belum dipilih ('.(int) $r->qty.')</span>';
            }

            return '<div>'.$badge.'</div>';
        })->implode('');
    }

    public function index(Request $request)
    {
        $records = fn () => $this->onlyGroupRepresentatives(StagingOut::query());

        $totalEntry = $records()->count();

        $totalQty = StagingOut::sum('qty');
        $customers = StagingOut::whereNotNull('customer')->where('customer', '!=', '')
            ->select('customer')->distinct()->orderBy('customer')->pluck('customer');

        $sudahPicking = $records()->whereNotNull('picking_date')->count();

        $belumPicking = $records()->whereNull('picking_date')->count();

        $belumDikirim = $records()->whereNull('delivery_receipt_date')->count();

        return view('staging_out', compact(
            'totalEntry',
            'totalQty',
            'sudahPicking',
            'belumPicking',
            'customers'
        ))->with([
            'activeFilter' => $request->query('filter'),
            'activeStatus' => $request->query('status'),
        ]);
    }

    public function data(Request $request)
    {
        $soKey = "COALESCE(NULLIF(staging_outs.so_number, ''), CONCAT('__single_', staging_outs.id))";

        $query = StagingOut::query()
            ->selectRaw($soKey.' as so_key, MAX(staging_outs.id) as latest_id')
            ->leftJoin('items', 'items.id', '=', 'staging_outs.item_id')
            ->groupByRaw($soKey);

        $this->onlyGroupRepresentatives($query);

        if (in_array($request->status, ['belum_picking', 'menunggu_picking'])) {
            $query->whereNull('staging_outs.picking_date');

        } elseif ($request->status === 'sudah_picking') {
            $query->whereNotNull('staging_outs.picking_date');

        } elseif ($request->status === 'siap_kirim') {
            $query->whereNotNull('staging_outs.picking_date')
                ->whereNull('staging_outs.delivery_receipt_date');

        } elseif ($request->status === 'belum_dikirim') {
            $query->whereNull('staging_outs.delivery_receipt_date');

        } elseif (in_array($request->status, ['sudah_dikirim', 'selesai'])) {
            $query->whereNotNull('staging_outs.delivery_receipt_date');

        } elseif ($request->status === 'terlambat') {
            $query->whereDate('staging_outs.delivery_instruction_date', '<', now())
                ->whereNull('staging_outs.delivery_receipt_date');
        }

        if ($request->filled('customer')) {
            $query->where('staging_outs.customer', $request->customer);
        }

        if ($request->filled('staging_location')) {
            if ($request->staging_location === 'belum_diisi') {
                $query->whereNull('staging_outs.staging_location');
            } elseif (in_array($request->staging_location, ['staging', 'packing', 'outbound'])) {
                $query->where('staging_outs.staging_location', $request->staging_location);
            }
        }

        $dateColumn = in_array($request->date_type, ['delivery_instruction_date', 'picking_date', 'delivery_receipt_date'])
            ? $request->date_type
            : 'delivery_instruction_date';

        if ($request->filled('start_date')) {
            $query->whereDate('staging_outs.'.$dateColumn, '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('staging_outs.'.$dateColumn, '<=', $request->end_date);
        }

        if ($request->overdue == 1) {
            $query->whereDate('staging_outs.delivery_instruction_date', '<', now())->whereNull('staging_outs.delivery_receipt_date');
        }

        if ($request->query('filter') === 'today') {
            $query->whereDate('staging_outs.delivery_instruction_date', now()->toDateString())
                ->whereNull('staging_outs.delivery_receipt_date');
        }

        if ($request->filled('order')) {
            $query->reorder();
        } else {
            $query->orderByRaw('MAX(staging_outs.id) DESC');
        }

        $response = DataTables::eloquent($query)

            ->addIndexColumn()

            ->filterColumn('so_number', function ($query, $keyword) {
                $query->whereRaw('LOWER(staging_outs.so_number) LIKE ?', ['%'.mb_strtolower($keyword).'%']);
            })
            ->filterColumn('item_code', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('items.item_code_internal', 'like', "%{$keyword}%")
                        ->orWhere('items.name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('line_item', function ($query, $keyword) {
                $query->where('staging_outs.line_item', 'like', "%{$keyword}%");
            })
            ->filterColumn('customer', function ($query, $keyword) {
                $query->where('staging_outs.customer', 'like', "%{$keyword}%");
            })

            ->orderColumn('so_number', 'MIN(staging_outs.so_number) $1')
            ->orderColumn('customer', 'MIN(staging_outs.customer) $1')
            ->orderColumn('qty', 'SUM(staging_outs.qty) $1')
            ->orderColumn('delivery_instruction_date', 'MIN(staging_outs.delivery_instruction_date) $1')

            ->rawColumns(['so_key'])

            ->make(true);

        $payload = $response->getData(true);
        $payload['data'] = $this->buildSoRows($payload['data'] ?? []);

        return response()->json($payload);
    }

    protected function buildSoRows(array $rows): array
    {
        if (empty($rows)) {
            return [];
        }

        $soNumbers = [];
        $singleIds = [];

        foreach ($rows as $r) {
            $key = (string) ($r['so_key'] ?? '');

            if (str_starts_with($key, '__single_')) {
                $singleIds[] = (int) substr($key, 9);
            } else {
                $soNumbers[] = $key;
            }
        }

        $items = StagingOut::with('item')
            ->where(function ($q) use ($soNumbers, $singleIds) {
                if ($soNumbers) {
                    $q->whereIn('so_number', $soNumbers);
                }
                if ($singleIds) {
                    $q->orWhereIn('id', $singleIds);
                }
            })
            ->orderBy('id')
            ->get();

        $bills = empty($soNumbers)
            ? collect()
            : PackingBill::whereIn('so_number', $soNumbers)->get()
                ->keyBy(fn ($b) => mb_strtolower((string) $b->so_number));

        $grouped = $items->groupBy(function ($item) {
            return mb_strtolower(
                ($item->so_number !== null && $item->so_number !== '')
                    ? (string) $item->so_number
                    : '__single_'.$item->id
            );
        });

        $out = [];

        foreach ($rows as $r) {
            $key = (string) ($r['so_key'] ?? '');
            $list = $grouped->get(mb_strtolower($key));

            if (! $list || $list->isEmpty()) {
                continue;
            }

            $out[] = $this->buildSoRow($r, $key, $list, $bills);
        }

        return $out;
    }

    protected function buildSoRow(array $base, string $key, $list, $bills): array
    {
        $ids = $list->pluck('id')->values()->all();
        $soNumber = optional($list->first(fn ($i) => filled($i->so_number)))->so_number;
        $isSingle = str_starts_with($key, '__single_');

        $records = $list
            ->filter(fn ($i) => $this->groupFor($i)->first()->id === $i->id)
            ->values();
        $count = max($records->count(), 1);

        $customers = $list->pluck('customer')->filter()->unique()->values();
        if ($customers->isEmpty()) {
            $customerHtml = '<span class="text-muted">-</span>';
        } else {
            $customerHtml = '<span class="fw-semibold">'.e($customers->first()).'</span>';
            if ($customers->count() > 1) {
                $customerHtml .= ' <span class="so-chip more" title="'.e($customers->implode(', ')).'">+'
                    .($customers->count() - 1).'</span>';
            }
        }

        $codes = $list->map(fn ($i) => $i->item ? $i->item->item_code_internal : null)
            ->filter()->unique()->values();
        $itemsHtml = '';
        foreach ($codes->take(2) as $code) {
            $itemsHtml .= '<span class="so-chip">'.e($code).'</span>';
        }
        if ($codes->count() > 2) {
            $itemsHtml .= '<span class="so-chip more">+'.($codes->count() - 2).' lagi</span>';
        }
        if ($itemsHtml === '') {
            $itemsHtml = '<span class="text-muted">-</span>';
        }

        $earliest = $list->pluck('delivery_instruction_date')->filter()->min();
        $dateHtml = $earliest
            ? '<div>'.e(\Carbon\Carbon::parse($earliest)->format('d M Y')).'</div>'
            : '<span class="text-muted">-</span>';

        $picked = $records
            ->filter(fn ($i) => $this->groupFor($i)->every(fn ($r) => $r->picking_date))
            ->count();
        if ($picked === 0) {
            $pill = '<span class="so-pill none"><i class="bx bx-time"></i> Belum picking</span>';
        } elseif ($picked === $count) {
            $pill = '<span class="so-pill done"><i class="bx bx-check-circle"></i> Sudah picking</span>';
        } else {
            $pill = '<span class="so-pill part"><i class="bx bx-loader-circle"></i> Picking '.$picked.'/'.$count.'</span>';
        }
        $percent = (int) round($picked / $count * 100);
        $statusHtml = $pill
            .'<div class="so-progress" title="'.$picked.' dari '.$count.' barang sudah picking"><span style="width:'.$percent.'%"></span></div>';

        $bill = $soNumber ? $bills->get(mb_strtolower((string) $soNumber)) : null;
        if (! $soNumber) {
            $billHtml = '<span class="text-muted small" title="Isi No. SO dulu agar bisa membuat bill">Tanpa No. SO</span>';
        } elseif ($bill) {
            $billHtml = '<a href="'.e(route('packing-bills.form', ['so_number' => $soNumber])).'" class="so-bill-btn has-bill" title="Lihat / edit bill">'
                .'<i class="bx bx-receipt"></i><span>Rp '.number_format((float) $bill->total, 0, ',', '.').'</span></a>';
        } else {
            $billHtml = '<a href="'.e(route('packing-bills.form', ['so_number' => $soNumber])).'" class="so-bill-btn" title="Buat bill packing">'
                .'<i class="bx bx-plus"></i><span>Buat Bill</span></a>';
        }

        $soHtml = '
            <div class="so-cell">
                <span class="so-icon"><i class="bx bx-receipt"></i></span>
                <div>
                    <div class="so-number">'.($soNumber ? e($soNumber) : '<span class="text-muted fw-normal">Tanpa No. SO</span>').'</div>
                    <div class="so-sub">'.$count.' barang</div>
                </div>
            </div>';

        return [
            'DT_RowIndex' => $base['DT_RowIndex'] ?? null,
            'so_key' => $key,
            'expander' => '<button type="button" class="so-toggle-btn" title="Lihat isi SO"><i class="bx bx-chevron-right"></i></button>',
            'checkbox' => '<input type="checkbox" class="so-checkbox" data-ids="'.e(json_encode($ids)).'">',
            'so_number' => $soHtml,
            'customer' => $customerHtml,
            'items_summary' => $itemsHtml,
            'qty' => '<span class="fw-bold">'.e($list->sum('qty')).'</span>',
            'delivery_instruction_date' => $dateHtml,
            'status_html' => $statusHtml,
            'bill_html' => $billHtml,
            'line_item' => '',
            'items_html' => $this->renderSoItemsTable($soNumber, $records, $picked),
        ];
    }

    protected function renderSoItemsTable($soNumber, $list, int $picked): string
    {
        $rowsHtml = '';

        foreach ($list->values() as $i => $item) {
            $qty = (int) $this->groupFor($item)->sum('qty');

            $rowsHtml .= '
                <tr>
                    <td class="text-center" style="width:44px"><input type="checkbox" class="row-checkbox" value="'.$item->id.'"></td>
                    <td class="text-center text-muted">'.($i + 1).'</td>
                    <td class="text-center">'.e($item->line_item ?: '-').'</td>
                    <td class="text-wrap">'.$this->renderItemCodeCell($item).'</td>
                    <td class="text-center fw-semibold">'.e((string) $qty).'</td>
                    <td class="text-center">'.$this->renderPickingCell($item).'</td>
                    <td class="text-center">'.$this->renderLocationCell($item).'</td>
                    <td class="text-center">'.e($item->do_number ?: '-').'</td>
                    <td class="text-center">'.$this->renderDeliveryCell($item).'</td>
                    <td>'.$this->renderActionCell($item).'</td>
                </tr>';
        }

        return '
        <div class="so-items-wrap">
            <div class="so-items-head">
                <span><i class="bx bx-package me-1"></i> Isi '.($soNumber ? 'SO '.e($soNumber) : 'baris ini').'
                    <span class="text-muted fw-normal">&middot; '.$list->count().' barang</span></span>
                <span class="text-muted fw-normal">'.$picked.' dari '.$list->count().' sudah picking</span>
            </div>
            <div class="table-responsive">
                <table class="table so-items-table align-middle">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-center">#</th>
                            <th class="text-center">Line Item</th>
                            <th>Kode Barang</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Tgl Picking</th>
                            <th class="text-center">Lokasi</th>
                            <th class="text-center">No. DO</th>
                            <th class="text-center">Tgl Resi Pengiriman</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>'.$rowsHtml.'</tbody>
                </table>
            </div>
        </div>';
    }

    protected function renderItemCodeCell(StagingOut $row): string
    {
        if (! $row->item) {
            return '-';
        }

        $group = $this->groupFor($row);

        if ($group->count() > 1) {
            return '
            <small class="fw-bold">'.e($row->item->item_code_internal).'</small>
            <div class="text-muted">'.e($row->item->name).'</div>
            <div class="mt-1 d-flex flex-column gap-1">'.$this->groupSourceLines($group).'</div>
        ';
        }

        if ($row->source_type === 'stock' && $row->location_id) {
            $sourceBadge = '<span class="badge bg-info-subtle text-info">Stok</span>';
        } elseif ($row->staging_in_id) {
            $sourceBadge = '<span class="badge bg-primary-subtle text-primary">Staging In</span>';
        } else {
            $sourceBadge = '<span class="badge bg-warning-subtle text-warning">Sumber belum dipilih</span>';
        }

        return '
            <small class="fw-bold">'.e($row->item->item_code_internal).'</small>
            <div class="text-muted">'.e($row->item->name).'</div>
            <div class="mt-1">'.$sourceBadge.'</div>
        ';
    }

    protected function renderPickingCell(StagingOut $row): string
    {
        if ($this->groupFor($row)->every(fn ($r) => $r->picking_date)) {
            return optional($row->picking_date)->format('d M Y');
        }

        return '
        <button type="button"
            class="btn btn-sm btn-confirm-picking btnConfirmPicking"
            data-id="'.$row->id.'"
            data-source-type="'.e($row->source_type).'"
            data-item-id="'.e($row->item_id).'"
            data-location-id="'.e($row->location_id).'"
            data-qty="'.e($row->qty).'"
            data-lot="'.e($row->lot).'"
            data-bs-toggle="modal"
            data-bs-target="#confirmPickingModal">

            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" class="me-1">
                <path d="M0 0h24v24H0z" fill="none"/>
                <path fill="currentColor" d="M20 3H4a1 1 0 0 0-1 1v16a1 1 0 0 0 1 1h16a1 1 0 0 0 1-1V4a1 1 0 0 0-1-1M9 17l-4-4l1.41-1.41L9 14.17l7.59-7.59L18 8z"/>
            </svg>
            Konfirmasi Picking
        </button>';
    }
    protected function renderLocationCell(StagingOut $row): string
    {
        if ($this->groupFor($row)->contains(fn ($r) => ! $r->picking_date)) {
            return '-';
        }

        $options = [
            '' => ['label' => '- Pilih -', 'class' => 'loc-empty'],
            'staging' => ['label' => '🟡 Staging', 'class' => 'loc-staging'],
            'packing' => ['label' => '🔵 Packing', 'class' => 'loc-packing'],
            'outbound' => ['label' => '🟢 Outbound', 'class' => 'loc-outbound'],
        ];

        $current = $row->staging_location ?? '';
        $currentClass = $options[$current]['class'] ?? 'loc-empty';

        $html = '<select class="form-select form-select-sm select-staging-location '.$currentClass.'" data-id="'.$row->id.'">';

        foreach ($options as $value => $opt) {
            $selected = $current === $value ? ' selected' : '';
            $html .= '<option value="'.$value.'" data-class="'.$opt['class'].'"'.$selected.'>'.$opt['label'].'</option>';
        }

        $html .= '</select>';

        return $html;
    }

    protected function renderDeliveryCell(StagingOut $row): string
    {
        if ($row->delivery_receipt_date) {
            return optional($row->delivery_receipt_date)->format('d M Y');
        }

        if ($this->groupFor($row)->contains(fn ($r) => ! $r->picking_date)) {
            return '-';
        }

        return '
        <button type="button"
            class="btn btn-sm btn-confirm-delivery btnConfirmDelivery"
            data-id="'.$row->id.'"
            data-source-type="'.e($row->source_type).'"
            data-item-id="'.e($row->item_id).'"
            data-location-id="'.e($row->location_id).'"
            data-qty="'.e($row->qty).'"
            data-lot="'.e($row->lot).'"
            data-bs-toggle="modal"
            data-bs-target="#confirmDeliveryModal">

            <i class="bx bxs-truck me-1"></i>
            Konfirmasi Kirim
        </button>';
    }

    protected function renderActionCell(StagingOut $row): string
    {
        $assignBtn = '';

        if ($row->item_id) {
            $hasSource = $this->groupFor($row)->contains(fn ($r) => $this->hasSource($r));
            $btnClass = $hasSource ? 'bg-info text-info' : 'bg-success text-success';
            $btnTitle = $hasSource ? 'Ubah sumber barang' : 'Pilih sumber barang (Staging In / Stok)';

            $assignBtn = '
        <button
            class="btn btn-sm '.$btnClass.' bg-opacity-10 rounded-3 border-0 btnAssignSource"
            type="button"
            title="'.$btnTitle.'"
            data-id="'.$row->id.'">
            <i class="bx '.($hasSource ? 'bx-git-compare' : 'bx-transfer').' fs-5"></i>
        </button>';
        }

        return '
    <div class="d-flex align-items-center gap-1">
'.$assignBtn.'

        <button
            class="btn btn-sm bg-primary bg-opacity-10 text-primary rounded-3 border-0 btnEdit"
            type="button"
            data-id="'.$row->id.'"
            data-bs-toggle="modal"
            data-bs-target="#editStagingOutModal">

            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
                <path d="M0 0h24v24H0z" fill="none"/>
                <path fill="currentColor"
                    d="m14.06 9l.94.94L5.92 19H5v-.92zm3.6-6c-.25 0-.51.1-.7.29l-1.83 1.83l3.75 3.75l1.83-1.83c.39-.39.39-1.04 0-1.41l-2.34-2.34c-.2-.2-.45-.29-.71-.29m-3.6 3.19L3 17.25V21h3.75L17.81 9.94z"/>
            </svg>

        </button>

        <form action="'.route('stagings-out.destroy', $row->id).'"
              method="POST"
              class="form-hapus m-0">

            '.csrf_field().'
            '.method_field('DELETE').'

            <button type="submit"
                class="btn btn-sm bg-danger bg-opacity-10 text-danger rounded-3 border-0">

                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" class="fs-5" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none"/>
                    <path fill="currentColor"
                        d="M6 19a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7H6zM8 9h8v10H8zm7.5-5l-1-1h-5l-1 1H5v2h14V4z"/>
                </svg>

            </button>

        </form>

    </div>';
    }

    public function updateLocation(Request $request, StagingOut $stagingOut)
    {
        $validated = $request->validate([
            'staging_location' => ['nullable', 'in:staging,packing,outbound'],
        ], [
            'staging_location.in' => 'Lokasi tidak valid.',
        ]);

        $group = $this->groupOf($stagingOut);

        if ($group->contains(fn ($r) => ! $r->picking_date)) {
            return response()->json([
                'success' => false,
                'message' => 'Lokasi hanya bisa diubah setelah tanggal picking diisi.',
            ], 422);
        }

        DB::transaction(function () use ($group, $validated) {
            foreach ($group as $row) {
                $changes = $this->history->diff($row, [
                    'staging_location' => $validated['staging_location'] ?? null,
                ]);

                $row->update([
                    'staging_location' => $validated['staging_location'] ?? null,
                ]);

                $this->history->logUpdated($row, $changes);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Lokasi berhasil diperbarui.',
        ]);
    }

    public function searchStock(Request $request)
    {
        $keyword = trim((string) $request->q);

        $items = Item::query()
            ->when($keyword !== '', function ($query) use ($keyword) {
                foreach (preg_split('/\s+/', $keyword) as $word) {
                    $like = '%'.mb_strtolower($word).'%';

                    $query->where(function ($q) use ($like) {
                        $q->whereRaw('LOWER(item_code_internal) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(item_code_customer) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(item_code_supplier) LIKE ?', [$like])
                            ->orWhereRaw('LOWER(name) LIKE ?', [$like]);
                    });
                }
            })
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json(
            $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->item_code_internal.' | '.$item->name,
                    'item_code' => $item->item_code_internal,
                    'item_name' => $item->name,
                    'item_code_customer' => $item->item_code_customer,
                    'item_code_supplier' => $item->item_code_supplier,
                ];
            })
        );
    }

    protected function currentStockRow(Request $request): ?StagingOut
    {
        if (! $request->filled('staging_out_id')) {
            return null;
        }

        $current = StagingOut::find($request->staging_out_id);

        if (! $current
            || $current->source_type !== 'stock'
            || ! $current->location_id
            || $current->item_id != $request->item_id) {
            return null;
        }

        return $current;
    }
    public function searchLocationForItem(Request $request)
    {
        $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'staging_out_id' => ['nullable', 'integer'],
        ]);

        $current = $this->currentStockRow($request);

        $locations = LocationStock::with('location')
            ->where('item_id', $request->item_id)
            ->where(function ($q) use ($current) {
                $q->where('quantity', '>', 0);

                if ($current) {
                    $q->orWhere('location_id', $current->location_id);
                }
            })
            ->get()
            ->groupBy('location_id')
            ->map(function ($rows) use ($current) {
                $first = $rows->first();
                $total = (float) $rows->sum('quantity');

                if ($current && $first->location_id == $current->location_id) {
                    $total += (float) $current->qty;
                }

                return [
                    'id' => $first->location_id,
                    'text' => optional($first->location)->location_name ?? ('Lokasi tidak diketahui'),
                    'total_quantity' => $total,
                ];
            })
            ->values();

        if ($current && ! $locations->contains('id', $current->location_id)) {
            $locations->push([
                'id' => $current->location_id,
                'text' => optional(Location::find($current->location_id))->location_name
                    ?? ('Lokasi tidak diketahui'),
                'total_quantity' => (float) $current->qty,
            ]);
        }

        return response()->json($locations->values());
    }

    public function searchLotForItemLocation(Request $request)
    {
        $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'staging_out_id' => ['nullable', 'integer'],
        ]);

        $current = $this->currentStockRow($request);
        $useCurrent = $current && $current->location_id == $request->location_id;
        $currentLot = $useCurrent ? ($current->lot ?: null) : null;

        $lots = LocationStock::where('item_id', $request->item_id)
            ->where('location_id', $request->location_id)
            ->where(function ($q) use ($useCurrent, $currentLot) {
                $q->where('quantity', '>', 0);

                if ($useCurrent) {
                    if ($currentLot === null) {
                        $q->orWhereNull('lot')->orWhere('lot', '');
                    } else {
                        $q->orWhere('lot', $currentLot);
                    }
                }
            })
            ->orderBy('lot')
            ->get()
            ->map(function ($row) use ($useCurrent, $current, $currentLot) {
                $qty = (float) $row->quantity;

                if ($useCurrent && ($row->lot ?: null) === $currentLot) {
                    $qty += (float) $current->qty;
                }

                return [
                    'id' => $row->lot ?? '',
                    'text' => $row->lot ?: '(Tanpa Lot)',
                    'quantity' => $qty,
                ];
            })
            ->values();

        if ($useCurrent && ! $lots->contains(fn ($l) => ($l['id'] ?: null) === $currentLot)) {
            $lots->push([
                'id' => $currentLot ?? '',
                'text' => $currentLot ?: '(Tanpa Lot)',
                'quantity' => (float) $current->qty,
            ]);
        }

        return response()->json($lots->values());
    }

    protected function hasSource(StagingOut $staging): bool
    {
        return ($staging->source_type === 'stock' && $staging->location_id) || (bool) $staging->staging_in_id;
    }

    protected function sourceIneligibleReason(StagingOut $staging): ?string
    {
        if (! $staging->item_id) {
            return 'Kode barang kosong, tidak bisa memilih sumber.';
        }

        if ((int) $staging->qty < 1) {
            return 'Qty 0, isi qty dulu.';
        }

        return null;
    }

    protected function groupIneligibleReason(StagingOut $row, int $totalQty): ?string
    {
        if (! $row->item_id) {
            return 'Kode barang kosong, tidak bisa memilih sumber.';
        }

        if ($totalQty < 1) {
            return 'Qty 0, isi qty dulu.';
        }

        return null;
    }

    public function sourceOptions(Request $request)
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        $groups = StagingOut::with('item')
            ->whereIn('id', $request->ids)
            ->orderBy('id')
            ->get()
            ->map(fn (StagingOut $row) => $this->groupOf($row))
            ->unique(fn (Collection $group) => $group->first()->id)
            ->values();

        $itemIds = $groups->map(fn (Collection $g) => $g->first()->item_id)->filter()->unique()->values();

        $stockByItem = LocationStock::with('location')
            ->whereIn('item_id', $itemIds)
            ->where('quantity', '>', 0)
            ->get()
            ->groupBy('item_id');

        $inByItem = StagingIn::whereIn('item_id', $itemIds)
            ->where('qty', '>', 0)
            ->orderBy('arrival_date')
            ->orderBy('id')
            ->get()
            ->groupBy('item_id');

        $locations = $this->locationNames();

        $fmt = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');

        $result = $groups->map(function (Collection $group) use ($stockByItem, $inByItem, $fmt, $locations) {
            $row = $group->first();
            $row->loadMissing('item');

            $totalQty = (int) $group->sum('qty');

            $reason = $this->groupIneligibleReason($row, $totalQty);
            $options = [];

            // Sumber yang sedang dipakai (bisa lebih dari satu).
            $currentLabels = [];

            foreach ($group as $r) {
                if ($r->source_type === 'stock' && $r->location_id) {
                    $currentLabels[] = 'Stok · '.($locations[$r->location_id] ?? 'Lokasi tidak diketahui')
                        .' · '.($r->lot ? 'Lot '.$r->lot : 'Tanpa Lot')
                        .($group->count() > 1 ? ' ('.(int) $r->qty.')' : '');
                } elseif ($r->staging_in_id) {
                    $curIn = StagingIn::find($r->staging_in_id);

                    $currentLabels[] = 'Staging In'.($curIn && $curIn->location ? ' · '.$curIn->location : '')
                        .($curIn && $curIn->po_number ? ' · PO '.$curIn->po_number : '')
                        .($group->count() > 1 ? ' ('.(int) $r->qty.')' : '');
                }
            }

            $current = $currentLabels
                ? ['type' => count($currentLabels) > 1 ? 'mixed' : ($group->first(fn ($r) => $this->hasSource($r))->staging_in_id ? 'staging_in' : 'stock'),
                    'label' => implode(' + ', $currentLabels)]
                : null;

            if (! $reason) {
                foreach ($inByItem->get($row->item_id, collect()) as $in) {
                    // Qty yang sedang dipakai record ini dari Staging In tsb
                    // dihitung tersedia lagi (dilepas dulu sebelum diterapkan ulang).
                    $heldHere = (float) $group->where('staging_in_id', $in->id)->sum('qty');
                    $isCurrent = $heldHere > 0;
                    $available = (float) $in->qty + $heldHere;

                    $chips = array_values(array_filter([
                        $isCurrent ? 'Saat ini' : null,
                        $in->po_number ? 'PO '.$in->po_number : null,
                        $in->supplier_origin,
                    ]));

                    $options[] = [
                        'group' => 'staging_in',
                        'title' => $in->location ?: 'Staging In',
                        'chips' => $chips,
                        // Keterangan/catatan Staging In (sesuaikan nama kolom bila berbeda).
                        'note' => trim((string) ($in->notes ?? $in->keterangan ?? $in->remarks ?? '')) ?: null,
                        'value' => 'in|'.$in->id,
                        'label' => ($in->location ?: 'Staging In').' — sisa '.$fmt($available),
                        'available' => $available,
                        // Boleh dipakai walau kurang dari qty baris: sisanya
                        // bisa diambil dari sumber lain (multi-sumber).
                        'enough' => $available > 0,
                    ];
                }

                foreach ($stockByItem->get($row->item_id, collect()) as $stock) {
                    $lot = $stock->lot ?: null;

                    $heldHere = (float) $group
                        ->filter(fn ($r) => $r->source_type === 'stock'
                            && $r->location_id
                            && (int) $r->location_id === (int) $stock->location_id
                            && ($r->lot ?: null) === $lot)
                        ->sum('qty');

                    $isCurrent = $heldHere > 0;
                    $available = (float) $stock->quantity + $heldHere;
                    $locName = optional($stock->location)->location_name ?? ('Lokasi tidak diketahui');

                    $options[] = [
                        'group' => 'stock',
                        'title' => $locName,
                        'chips' => array_values(array_filter([
                            $isCurrent ? 'Saat ini' : null,
                            $lot ? 'Lot '.$lot : 'Tanpa Lot',
                        ])),
                        'value' => 'stock|'.$stock->location_id.'|'.($lot ?? ''),
                        'label' => $locName.' · '.($lot ? 'Lot '.$lot : 'Tanpa Lot').' — sisa '.$fmt($available),
                        'available' => $available,
                        'enough' => $available > 0,
                    ];
                }
            }

            return [
                'id' => $row->id,
                'so_number' => $row->so_number,
                'customer' => $row->customer,
                'line_item' => $row->line_item,
                'item_code' => optional($row->item)->item_code_internal,
                'item_name' => optional($row->item)->name,
                'qty' => $totalQty,
                'eligible' => $reason === null,
                'reason' => $reason,
                'current' => $current,
                'options' => $options,
            ];
        })->values();

        return response()->json(['rows' => $result]);
    }

    public function assignSource(Request $request)
    {
        $validated = $request->validate([
            'assignments' => ['required', 'array', 'min:1'],
            'assignments.*.id' => ['required', 'integer', 'distinct', 'exists:staging_outs,id'],
            // Format lama: 1 sumber (source). Format baru: banyak sumber (allocations).
            'assignments.*.source' => ['nullable', 'string', 'max:300'],
            'assignments.*.allocations' => ['nullable', 'array', 'min:1'],
            'assignments.*.allocations.*.source' => ['required', 'string', 'max:300'],
            'assignments.*.allocations.*.qty' => ['required', 'integer', 'min:1'],
        ], [
            'assignments.required' => 'Belum ada perubahan yang dipilih.',
            'assignments.min' => 'Belum ada perubahan yang dipilih.',
            'assignments.*.id.exists' => 'Data staging out tidak ditemukan.',
            'assignments.*.allocations.*.source.required' => 'Sumber belum dipilih.',
            'assignments.*.allocations.*.qty.required' => 'Qty tiap sumber wajib diisi.',
            'assignments.*.allocations.*.qty.integer' => 'Qty tiap sumber harus angka bulat.',
            'assignments.*.allocations.*.qty.min' => 'Qty tiap sumber minimal 1.',
        ]);

        try {
            $result = DB::transaction(function () use ($validated) {
                $assigned = 0;
                $reverted = 0;

                foreach ($validated['assignments'] as $assignment) {
                    $staging = StagingOut::with('item')->lockForUpdate()->findOrFail($assignment['id']);

                    // Satu record bisa terdiri dari beberapa baris (1 per sumber).
                    $group = $this->groupOf($staging, true);
                    $staging = $group->first();

                    $label = 'No. SO '.($staging->so_number ?: '-')
                        .' / '.(optional($staging->item)->item_code_internal ?: '-');

                    try {
                        if (($assignment['source'] ?? null) === 'none') {
                            if (! $group->contains(fn ($r) => $this->hasSource($r))) {
                                throw new \RuntimeException('Sumber memang belum dipilih.');
                            }

                            foreach ($group as $r) {
                                if ($this->hasSource($r)) {
                                    $this->releaseSource($r);
                                }
                            }

                            $reverted++;

                            continue;
                        }

                        if ($reason = $this->groupIneligibleReason($staging, (int) $group->sum('qty'))) {
                            throw new \RuntimeException($reason);
                        }

                        foreach ($group as $r) {
                            if ($this->hasSource($r)) {
                                $this->releaseSource($r);
                            }

                            $r->refresh();
                        }

                        $allocations = $assignment['allocations'] ?? [];

                        if (! $allocations && ! empty($assignment['source'])) {
                            $allocations = [['source' => $assignment['source'], 'qty' => null]];
                        }

                        if (! $allocations) {
                            throw new \RuntimeException('Sumber belum dipilih.');
                        }

                        $this->assignAllocations($group, $allocations);

                        // Sumber sudah dipilih: tgl picking yang masih kosong diisi hari ini.
                        $today = now()->toDateString();

                        foreach ($this->groupOf($staging, true) as $r) {
                            if (! $r->picking_date) {
                                $changes = $this->history->diff($r, ['picking_date' => $today]);

                                $r->update(['picking_date' => $today]);

                                $this->history->logUpdated($r, $changes);
                            }
                        }

                        $assigned++;
                    } catch (\RuntimeException $e) {
                        throw new \RuntimeException("{$label}: ".$e->getMessage());
                    }
                }

                return compact('assigned', 'reverted');
            });

            $messages = [];

            if ($result['assigned']) {
                $messages[] = $result['assigned'].' data berhasil ditetapkan sumbernya';
            }

            if ($result['reverted']) {
                $messages[] = $result['reverted'].' data dikembalikan ke sumber belum dipilih';
            }

            return response()->json([
                'success' => true,
                'message' => implode(', ', $messages).'.',
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    protected function assignAllocations(Collection $rows, array $allocations): void
    {
        $rows = $rows->values();
        $total = (int) $rows->sum('qty');
        $merged = [];

        foreach ($allocations as $allocation) {
            $source = trim((string) ($allocation['source'] ?? ''));

            if ($source === '') {
                throw new \RuntimeException('Sumber belum dipilih.');
            }

            $qty = isset($allocation['qty']) ? (int) $allocation['qty'] : $total;

            $merged[$source] = ($merged[$source] ?? 0) + $qty;
        }

        $sum = array_sum($merged);

        if ($sum !== $total) {
            throw new \RuntimeException("Total qty semua sumber ({$sum}) harus sama dengan qty barang ({$total}).");
        }

        $template = $rows->first()->replicate();
        $index = 0;

        foreach ($merged as $source => $qty) {
            $existing = $rows->get($index++);

            if ($existing) {
                $target = $existing;

                if ((int) $target->qty !== $qty) {
                    $changes = $this->history->diff($target, ['qty' => $qty]);

                    $target->qty = $qty;
                    $target->save();

                    $this->history->logUpdated($target, $changes);
                }
            } else {
                $target = $template->replicate();
                $target->forceFill([
                    'qty' => $qty,
                    'source_type' => 'external',
                    'location_id' => null,
                    'lot' => null,
                    'staging_in_id' => null,
                ])->save();

                $this->history->logCreated($target);
            }

            $this->assignBySourceString($target, (string) $source);
        }

        foreach ($rows->slice(count($merged)) as $unused) {
            $this->history->logDeleted($unused);
            $unused->delete();
        }
    }

    protected function assignBySourceString(StagingOut $staging, string $source): void
    {
        $parts = explode('|', $source, 3);

        if (($parts[0] ?? null) === 'in') {
            $this->assignFromStagingIn($staging, (int) ($parts[1] ?? 0));
        } elseif (($parts[0] ?? null) === 'stock') {
            $this->assignFromStock($staging, (int) ($parts[1] ?? 0), ($parts[2] ?? '') ?: null);
        } else {
            throw new \RuntimeException('Sumber tidak valid.');
        }
    }

    protected function releaseSource(StagingOut $staging): void
    {
        if ($staging->source_type === 'stock' && $staging->location_id) {
            $changes = $this->history->diff($staging, [
                'source_type' => 'external',
                'location_id' => null,
                'lot' => null,
            ]);

            $this->reverseStockOut($staging);

            $staging->source_type = 'external';
            $staging->location_id = null;
            $staging->lot = null;
            $staging->save();

            $this->history->logUpdated($staging, $changes);

            return;
        }

        if ($staging->staging_in_id) {
            $this->returnToStagingIn($staging);

            $staging->refresh();

            if ($staging->staging_in_id) {
                $staging->forceFill(['staging_in_id' => null])->save();
            }
        }
    }

    protected function assignFromStock(StagingOut $staging, int $locationId, ?string $lot): void
    {
        if (! $locationId) {
            throw new \RuntimeException('Lokasi stok belum dipilih.');
        }

        $changes = $this->history->diff($staging, [
            'source_type' => 'stock',
            'location_id' => $locationId,
            'lot' => $lot,
        ]);

        $staging->source_type = 'stock';
        $staging->location_id = $locationId;
        $staging->lot = $lot;
        $staging->save();

        $this->applyStockOut($staging);

        $this->history->logUpdated($staging, $changes);
    }

    protected function assignFromStagingIn(StagingOut $staging, int $stagingInId): void
    {
        $in = $stagingInId ? StagingIn::lockForUpdate()->find($stagingInId) : null;

        if (! $in) {
            throw new \RuntimeException('Data Staging In tidak ditemukan atau sudah habis.');
        }

        if ((int) $in->item_id !== (int) $staging->item_id) {
            throw new \RuntimeException('Barang di Staging In berbeda dengan barang staging out.');
        }

        $qty = (int) $staging->qty;

        if ($qty > (int) $in->qty) {
            throw new \RuntimeException("Qty melebihi sisa di Staging In (sisa {$in->qty}).");
        }

        app(StagingInReturnService::class)->link($staging, $in);

        $remaining = (int) $in->qty - $qty;

        app(StagingInHistoryService::class)->logMovedToStagingOut($in, $qty, max($remaining, 0), [
            'so_number' => $staging->so_number,
            'customer' => $staging->customer,
            'line_item' => $staging->line_item,
            'delivery_instruction_date' => optional($staging->delivery_instruction_date)->format('Y-m-d'),
            'staging_location' => $staging->staging_location,
            'staging_out_id' => $staging->id,
        ]);

        if ($remaining > 0) {
            $in->update(['qty' => $remaining]);
        } else {
            $in->delete();
        }
    }

    public function template()
    {
        return Excel::download(new StagingOutTemplateExport, 'template_staging_out.xlsx');
    }

    public function export(Request $request)
    {
        $filename = 'staging_out_'.now()->format('d-m-Y').'.xlsx';

        return Excel::download(new StagingOutExport($request), $filename);
    }

    public function import(Request $request)
    {
        if ($request->input('source') === 'stock') {
            return $this->importStock($request);
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
            'mode' => ['required', Rule::in(['reset', 'append'])],
        ], [
            'file.required' => 'File wajib diupload',
            'file.mimes' => 'File harus berformat .xlsx atau .xls',
            'file.max' => 'Ukuran file maksimal 5MB',
            'mode.required' => 'Pilih mode import terlebih dahulu (reset atau tambahkan)',
            'mode.in' => 'Mode import tidak valid',
        ]);

        $isReset = $request->mode === 'reset';

        try {

            if ($isReset) {

                DB::transaction(function () {
                    $existing = StagingOut::all();

                    $this->history->logResetByImport($existing);

                    foreach ($existing as $staging) {
                        if ($staging->source_type === 'stock' && $staging->item_id && $staging->location_id) {
                            $this->reverseStockOut($staging);
                        }

                        $this->returnToStagingIn($staging);
                    }

                    StagingOut::query()->delete();
                });
            }

            Excel::import(app(StagingOutImport::class), $request->file('file'));
        } catch (\Exception $e) {
            return redirect()
                ->route('stagings-out.index')
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('stagings-out.index')
            ->with('success', $isReset
                ? 'Data staging lama berhasil diganti dengan data dari file'
                : 'Data staging berhasil ditambahkan dari file');
    }

    public function importStock(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120'],
        ], [
            'file.required' => 'File wajib diupload',
            'file.mimes' => 'File harus berformat .xlsx atau .xls',
            'file.max' => 'Ukuran file maksimal 5MB',
        ]);

        $import = app(StagingOutStockImport::class);

        try {
            Excel::import($import, $request->file('file'));
        } catch (\Exception $e) {
            return redirect()
                ->route('stagings-out.index')
                ->with('error', $e->getMessage());
        }
        if ($import->imported === 0 && empty($import->errors)) {
            return redirect()
                ->route('stagings-out.index')
                ->with('error', 'Tidak ada baris yang bisa diimpor. Pastikan file tidak kosong dan formatnya sesuai template.');
        }

        if (empty($import->errors)) {
            return redirect()
                ->route('stagings-out.index')
                ->with('success', "{$import->imported} baris staging out dari stok berhasil diimpor.");
        }

        $summary = "{$import->imported} baris berhasil diimpor, ".count($import->errors).' baris dilewati:';

        return redirect()
            ->route('stagings-out.index')
            ->with('error', $summary."\n\n".implode("\n\n", $import->errors));
    }

    protected function baseValidationRules(): array
    {
        return [
            'so_number' => ['nullable', 'max:255'],
            'customer' => ['nullable', 'max:255'],
            'source_type' => ['required', 'in:external,stock'],
            'item_id' => ['nullable', 'exists:items,id'],
            'line_item' => ['nullable', 'max:255'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'lot' => ['nullable', 'max:255'],
            'delivery_instruction_date' => ['nullable', 'date'],
            'picking_date' => ['nullable', 'date'],
            'do_number' => ['nullable', 'max:255'],
            'delivery_receipt_date' => ['nullable', 'date'],
        ];
    }
    protected function validateStockAvailability(ValidatorContract $validator, Request $request, ?StagingOut $current = null): void
    {
        $validator->after(function ($validator) use ($request, $current) {
            if ($request->source_type !== 'stock') {
                return;
            }

            if (! $request->filled('item_id')) {
                $validator->errors()->add('item_id', 'Barang wajib dipilih untuk sumber stok.');
            }

            if (! $request->filled('location_id')) {
                $validator->errors()->add('location_id', 'Lokasi wajib dipilih untuk sumber stok.');
            }

            if ($validator->errors()->has('qty')) {
                return;
            }

            if (! $request->filled('qty') || (int) $request->qty < 1) {
                $validator->errors()->add('qty', 'Qty wajib diisi dan lebih dari 0 untuk sumber stok.');
            }

            if (! $request->filled('item_id') || ! $request->filled('location_id') || ! $request->filled('qty')) {
                return;
            }

            $stock = LocationStock::where('item_id', $request->item_id)
                ->where('location_id', $request->location_id)
                ->where('lot', $request->lot ?: null)
                ->first();

            $available = (float) optional($stock)->quantity;

            if ($current
                && $current->source_type === 'stock'
                && $current->item_id == $request->item_id
                && $current->location_id == $request->location_id
                && $current->lot == ($request->lot ?: null)
            ) {
                $available += (float) $current->qty;
            }

            if ((float) $request->qty > $available) {
                $validator->errors()->add(
                    'qty',
                    'Qty melebihi stok yang tersedia. Sisa stok: '.rtrim(rtrim(number_format($available, 2, '.', ''), '0'), '.')
                );
            }
        });
    }

    protected function validateEditStockQty(ValidatorContract $validator, Request $request, StagingOut $current): void
    {
        $validator->after(function ($validator) use ($request, $current) {
            if ($current->source_type !== 'stock' || ! $current->location_id || ! $current->item_id) {
                return;
            }

            if ($validator->errors()->has('qty') || ! $request->filled('qty')) {
                return;
            }

            if ((int) $request->input('item_id') !== (int) $current->item_id) {
                return;
            }

            $stock = LocationStock::where('item_id', $current->item_id)
                ->where('location_id', $current->location_id)
                ->where('lot', $current->lot ?: null)
                ->first();

            $available = (float) optional($stock)->quantity + (float) $current->qty;

            if ((float) $request->qty > $available) {
                $validator->errors()->add(
                    'qty',
                    'Qty melebihi stok di sumber saat ini. Maksimal: '.rtrim(rtrim(number_format($available, 2, '.', ''), '0'), '.')
                );
            }
        });
    }

    protected function lockLocationStock(int $itemId, int $locationId, ?string $lot)
    {
        return LocationStock::query()
            ->where('item_id', $itemId)
            ->where('location_id', $locationId)
            ->where('lot', $lot)
            ->lockForUpdate()
            ->first();
    }

    protected function applyStockOut(StagingOut $stagingOut): void
    {
        $stock = $this->lockLocationStock((int) $stagingOut->item_id, (int) $stagingOut->location_id, $stagingOut->lot ?: null);

        if (! $stock || (float) $stock->quantity < (float) $stagingOut->qty) {
            throw new \RuntimeException('Stok tidak mencukupi untuk item/lokasi/lot yang dipilih.');
        }

        $stock->quantity = (float) $stock->quantity - (float) $stagingOut->qty;
        $stock->save();

        StockMutation::create([
            'item_id' => $stagingOut->item_id,
            'location_id' => $stagingOut->location_id,
            'lot' => $stagingOut->lot,
            'transaction_date' => $stagingOut->delivery_receipt_date
                ?? $stagingOut->picking_date
                ?? $stagingOut->delivery_instruction_date
                ?? now(),
            'transaction_number' => $stagingOut->so_number,
            'description' => null,
            'qty_in' => 0,
            'qty_out' => $stagingOut->qty,
            'qty_balance' => $stock->quantity,
        ]);
    }

    protected function reverseStockOut(StagingOut $stagingOut): void
    {
        app(StagingOutStockService::class)->reverse($stagingOut);
    }

    protected function returnToStagingIn(StagingOut $stagingOut): void
    {
        if ($stagingOut->staging_in_id) {
            app(StagingInReturnService::class)->giveBack($stagingOut, (int) $stagingOut->qty);
        }
    }

    protected function stockOutDescription(StagingOut $stagingOut): string
    {
        return app(StagingOutStockService::class)->description($stagingOut);
    }
    protected function batchValidationRules(): array
    {
        $rules = [
            'so_number' => ['nullable', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
        ];

        foreach (Arr::except($this->baseValidationRules(), ['so_number']) as $field => $rule) {
            $rules["items.*.{$field}"] = $rule;
        }

        $rules['items.*.item_id'] = ['required', 'exists:items,id'];
        $rules['items.*.qty'] = ['required', 'integer', 'min:1'];

        return $rules;
    }

    protected function validateBatchStockAvailability(ValidatorContract $validator, Request $request): void
    {
        $validator->after(function ($validator) use ($request) {
            $used = [];

            foreach ((array) $request->input('items', []) as $i => $line) {
                if (($line['source_type'] ?? null) !== 'stock') {
                    continue;
                }

                $key = "items.{$i}";
                $errors = $validator->errors();

                if ($errors->has("{$key}.item_id") || $errors->has("{$key}.qty")) {
                    continue;
                }

                $itemId = $line['item_id'] ?? null;
                $locationId = $line['location_id'] ?? null;
                $qty = $line['qty'] ?? null;
                $lot = ($line['lot'] ?? null) ?: null;

                if (! $locationId) {
                    $errors->add(
                        "{$key}.location_id",
                        'Lokasi belum dipilih. Pilih lokasi yang memiliki stok barang ini.'
                    );

                    continue;
                }

                $stock = LocationStock::where('item_id', $itemId)
                    ->where('location_id', $locationId)
                    ->where('lot', $lot)
                    ->first();

                $groupKey = $itemId.'|'.$locationId.'|'.($lot ?? '');
                $available = (float) optional($stock)->quantity - ($used[$groupKey] ?? 0);

                if ((float) $qty > $available) {
                    $left = rtrim(rtrim(number_format(max($available, 0), 2, '.', ''), '0'), '.');

                    $errors->add(
                        "{$key}.qty",
                        $left === '' || $left === '0'
                            ? 'Stok tidak tersedia untuk barang/lokasi/lot ini (sisa stok: 0).'
                            : "Qty melebihi stok yang tersedia. Sisa stok: {$left}."
                    );

                    continue;
                }

                $used[$groupKey] = ($used[$groupKey] ?? 0) + (float) $qty;
            }
        });
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), $this->batchValidationRules(), [
            'so_number.max' => 'No. SO terlalu panjang (maksimal 255 karakter).',

            'items.required' => 'Belum ada barang. Tambahkan minimal satu barang.',
            'items.min' => 'Belum ada barang. Tambahkan minimal satu barang.',
            'items.*.source_type.required' => 'Sumber barang belum dipilih (Eksternal / Dari Stok).',
            'items.*.source_type.in' => 'Sumber barang tidak valid.',
            'items.*.item_id.required' => 'Barang belum dipilih.',
            'items.*.item_id.exists' => 'Barang yang dipilih tidak ditemukan di data master.',
            'items.*.location_id.exists' => 'Lokasi yang dipilih tidak ditemukan.',
            'items.*.qty.required' => 'Qty belum diisi.',
            'items.*.qty.integer' => 'Qty harus berupa angka bulat (tanpa koma).',
            'items.*.qty.min' => 'Qty minimal 1.',
            'items.*.customer.max' => 'Nama customer terlalu panjang (maksimal 255 karakter).',
            'items.*.line_item.max' => 'Line item terlalu panjang (maksimal 255 karakter).',
            'items.*.lot.max' => 'Lot terlalu panjang (maksimal 255 karakter).',
            'items.*.do_number.max' => 'No. DO terlalu panjang (maksimal 255 karakter).',
            'items.*.delivery_instruction_date.date' => 'Tgl instruksi kirim tidak valid.',
            'items.*.picking_date.date' => 'Tgl picking tidak valid.',
            'items.*.delivery_receipt_date.date' => 'Tgl resi pengiriman tidak valid.',
        ]);

        $this->validateBatchStockAvailability($validator, $request);

        $validated = $validator->validate();

        try {
            $deliveredCount = DB::transaction(function () use ($validated) {
                $delivered = 0;

                foreach ($validated['items'] as $line) {
                    $line['so_number'] = $validated['so_number'] ?? null;

                    if ($line['source_type'] === 'external') {
                        $line['location_id'] = null;
                        $line['lot'] = null;
                    }

                    $staging = StagingOut::create($line);

                    if ($staging->source_type === 'stock') {
                        $this->applyStockOut($staging);
                    }

                    $this->history->logCreated($staging);

                    if (! empty($line['delivery_receipt_date'])) {
                        $this->history->logAutoDelivered(
                            $staging,
                            $staging->do_number,
                            $staging->delivery_receipt_date
                        );

                        if (! empty($line['picking_date'])) {
                            StagingOutHistory::firstOrCreateForStaging($staging)
                                ->update(['picking_date' => $line['picking_date']]);
                        }

                        $staging->delete();

                        $delivered++;
                    }
                }

                return $delivered;
            });

            $total = count($validated['items']);

            $message = $total.' barang berhasil disimpan';

            if ($deliveredCount > 0) {
                $message .= $deliveredCount === $total
                    ? ' dan langsung dipindahkan ke history (sudah terkirim).'
                    : ", {$deliveredCount} di antaranya langsung dipindahkan ke history (sudah terkirim).";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function edit(StagingOut $stagingOut)
    {
        $stagingOut->loadMissing('item');

        $group = $this->groupOf($stagingOut);

        return response()->json([
            'id' => $stagingOut->id,
            'so_number' => $stagingOut->so_number,
            'customer' => $stagingOut->customer,
            'source_type' => $stagingOut->source_type,
            'item_id' => $stagingOut->item_id,
            'item_code' => optional($stagingOut->item)->item_code_internal,
            'item_name' => optional($stagingOut->item)->name,
            'location_id' => $stagingOut->location_id,
            'lot' => $stagingOut->lot,
            'line_item' => $stagingOut->line_item,
            'qty' => (int) $group->sum('qty'),
            'qty_locked' => $group->count() > 1,
            'delivery_instruction_date' => optional($stagingOut->delivery_instruction_date)->format('Y-m-d'),
            'picking_date' => optional($stagingOut->picking_date)->format('Y-m-d'),
            'do_number' => $stagingOut->do_number,
            'delivery_receipt_date' => optional($stagingOut->delivery_receipt_date)->format('Y-m-d'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StagingOut $stagingOut)
    {
        $rules = Arr::except($this->baseValidationRules(), ['source_type', 'location_id', 'lot']);

        $rules['qty'] = ['required', 'integer', 'min:1'];

        if ($this->groupOf($stagingOut)->count() > 1) {
            $request->merge(['qty' => $stagingOut->qty]);
        }

        $validator = Validator::make($request->all(), $rules, [
            'item_id.exists' => 'Barang tidak ditemukan',
            'qty.required' => 'Qty wajib diisi.',
            'qty.integer' => 'Qty harus berupa angka bulat (tanpa koma).',
            'qty.min' => 'Qty minimal 1.',
        ]);

        $this->validateEditStockQty($validator, $request, $stagingOut);

        $validated = $validator->validate();

        try {
            $syncedCount = 0;

            $justDelivered = DB::transaction(function () use ($validated, $stagingOut, &$syncedCount) {

                $rows = $this->groupOf($stagingOut, true);
                $delivered = false;

                $soNumber = $stagingOut->so_number;
                $oldCustomer = (string) $stagingOut->customer;
                $groupIds = $rows->pluck('id')->all();

                $syncCustomer = filled($soNumber)
                    && array_key_exists('customer', $validated)
                    && (($validated['so_number'] ?? null) === $soNumber)
                    && $oldCustomer !== (string) ($validated['customer'] ?? '');

                foreach ($rows as $row) {
                    $data = $validated;

                    if ($rows->count() > 1) {
                        $data['qty'] = $row->qty;
                    }

                    $delivered = $this->applyUpdate($row, $data) || $delivered;
                }

                if ($syncCustomer) {
                    $newCustomer = $validated['customer'] ?? null;

                    $siblings = StagingOut::where('so_number', $soNumber)
                        ->whereNotIn('id', $groupIds)
                        ->get();

                    foreach ($siblings as $sibling) {
                        if ((string) $sibling->customer === (string) $newCustomer) {
                            continue;
                        }

                        $siblingChanges = $this->history->diff($sibling, ['customer' => $newCustomer]);
                        $sibling->update(['customer' => $newCustomer]);
                        $this->history->logUpdated($sibling, $siblingChanges);
                        $syncedCount++;
                    }
                }

                return $delivered;
            });

            return response()->json([
                'success' => true,
                'message' => $justDelivered
                    ? 'Tanggal kirim berhasil dikonfirmasi. Data dipindahkan ke history.'
                    : ($syncedCount > 0
                        ? 'Data berhasil diperbarui. Customer ikut diperbarui di '.$syncedCount.' barang lain dalam SO ini.'
                        : 'Data berhasil diperbarui.'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    protected function applyUpdate(StagingOut $stagingOut, array $validated): bool
    {
        if (array_key_exists('item_id', $validated)
            && (int) $validated['item_id'] !== (int) $stagingOut->item_id
            && $this->hasSource($stagingOut)) {
            $this->releaseSource($stagingOut);
            $stagingOut->refresh();
        }

        $changes = $this->history->diff($stagingOut, $validated);

        $justDelivered = is_null($stagingOut->delivery_receipt_date)
            && array_key_exists('delivery_receipt_date', $validated)
            && ! is_null($validated['delivery_receipt_date']);

        $wasStock = $stagingOut->source_type === 'stock' && $stagingOut->item_id && $stagingOut->location_id;

        if ($wasStock) {
            $this->reverseStockOut($stagingOut);
        }

        if ($stagingOut->staging_in_id && array_key_exists('qty', $validated)) {
            app(StagingInReturnService::class)->syncQty(
                $stagingOut,
                (int) $stagingOut->qty,
                (int) ($validated['qty'] ?? 0)
            );
        }

        $stagingOut->update($validated);

        if ($stagingOut->source_type === 'stock') {
            $this->applyStockOut($stagingOut);
        }

        $this->history->logUpdated($stagingOut, $changes);

        if ($justDelivered) {
            $this->history->logAutoDelivered(
                $stagingOut,
                $stagingOut->do_number,
                $stagingOut->delivery_receipt_date
            );

            if ($stagingOut->picking_date) {
                StagingOutHistory::firstOrCreateForStaging($stagingOut)
                    ->update(['picking_date' => $stagingOut->picking_date]);
            }

            $stagingOut->delete();
        }

        return $justDelivered;
    }
    public function destroy(StagingOut $stagingOut)
    {
        try {
            DB::transaction(function () use ($stagingOut) {
                foreach ($this->groupOf($stagingOut, true) as $row) {
                    if ($row->source_type === 'stock' && $row->item_id && $row->location_id) {
                        $this->reverseStockOut($row);
                    }

                    $this->returnToStagingIn($row);

                    $this->history->logDeleted($row);

                    $row->delete();
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:staging_outs,id',
        ]);

        try {
            $count = DB::transaction(function () use ($request) {
                $stagings = $this->expandGroups(StagingOut::whereIn('id', $request->ids)->get());

                foreach ($stagings as $staging) {
                    if ($staging->source_type === 'stock' && $staging->item_id && $staging->location_id) {
                        $this->reverseStockOut($staging);
                    }

                    $this->returnToStagingIn($staging);
                }

                $this->history->logBulkDeleted($stagings);

                StagingOut::whereIn('id', $stagings->pluck('id'))->delete();

                return count(array_unique($request->ids));
            });

            return response()->json([
                'success' => true,
                'message' => $count.' data berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}