<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StagingInHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'staging_in_id',
        'item_id',
        'po_number',
        'supplier_origin',
        'location',
        'arrival_date',
        'incoterms',
        'initial_qty',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'arrival_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(StagingInHistoryDetail::class)->latest();
    }

    /**
     * Ambil (atau buat) header history untuk sebuah baris StagingIn.
     * Dipanggil dari StagingInHistoryService — tidak dipakai langsung
     * di controller.
     */
    public static function firstOrCreateForStaging(StagingIn $staging): self
    {
        return static::firstOrCreate(
            ['staging_in_id' => $staging->id],
            [
                'item_id' => $staging->item_id,
                'po_number' => $staging->po_number,
                'supplier_origin' => $staging->supplier_origin,
                'location' => $staging->location,
                'arrival_date' => $staging->arrival_date,
                'incoterms' => $staging->incoterms,
                'initial_qty' => $staging->qty,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]
        );
    }
}