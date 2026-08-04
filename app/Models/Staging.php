<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staging extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'arrival_date',
        'supplier_origin',
        'item_owner',
        'item_code',
        'item_name',
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
}