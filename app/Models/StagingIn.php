<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'arrival_date',
        'supplier_origin',
        'item_id',
        'qty',
        'location',
        'notes',
        'status',
        'incoterms',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'qty' => 'integer',
    ];

    /**
     * Daftar pilihan lokasi (enum) — dipakai bareng di controller & view (dropdown).
     */
    public const LOCATIONS = [
        'Inbound shipment',
        'Temporary hold / repair 1',
        'Temporary hold / repair 2',
        'Temporary hold / repair 3',
    ];

    const STATUSES = [
        'menunggu request kirim',
        'menunggu request packing',
        'menunggu sertifikat',
        'menunggu dokumen pelengkap',
        'rusak',
        'tidak lengkap',
        'salah ukuran',
        'batal',
    ];

    /**
     * Daftar pilihan incoterms (enum) — dipakai bareng di controller & view (dropdown).
     */
    public const INCOTERMS = [
        'VHS',
        'DDP',
        'SMELTER',
        'NON FI',
        'FLUKE',
        'NORD-LOCK',
    ];

    /**
     * Gabungan status default (STATUSES) dengan status lain yang sudah
     * pernah tersimpan di tabel — termasuk status baru yang masuk lewat
     * import dan belum ada di daftar STATUSES. Dipakai untuk mengisi
     * dropdown/select di UI supaya status baru tetap muncul sebagai pilihan.
     */
    public static function allStatusOptions(): array
    {
        $fromData = static::query()
            ->whereNotNull('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status')
            ->all();

        return collect(self::STATUSES)
            ->merge($fromData)
            ->unique()
            ->values()
            ->all();
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouseLocation()
    {
        return $this->belongsTo(Location::class, 'warehouse_location_id');
    }
}