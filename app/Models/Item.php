<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Location;


class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code_internal',
        'item_code_supplier',
        'item_code_customer',
        'name',
        'description',
        'location_id',
        'vendor_id',
        'incoterms',
        'quantity',
    ];

    public const INCOTERMS = ['VHS', 'FLUKE', 'NORD LOCK'];

    // Nilai khusus untuk filter saja (tidak disimpan di database / bukan opsi select form)
    public const FILTER_NON_VHS = 'NON_VHS';

    /**
     * Filter bersama untuk tabel (data) dan export, supaya hasilnya selalu sama.
     * Key: vendor_id, incoterms, search
     */
    public function scopeApplyFilters($query, array $filters)
    {
        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (! empty($filters['incoterms'])) {
            if ($filters['incoterms'] === self::FILTER_NON_VHS) {
                // semua yang bukan VHS (termasuk yang incoterms-nya masih kosong)
                $query->where(function ($q) {
                    $q->whereNull('incoterms')->orWhere('incoterms', '!=', 'VHS');
                });
            } else {
                $query->where('incoterms', $filters['incoterms']);
            }
        }

        if (isset($filters['search']) && trim($filters['search']) !== '') {
            $keyword = '%'.trim($filters['search']).'%';

            $query->where(function ($q) use ($keyword) {
                $q->where('item_code_internal', 'LIKE', $keyword)
                    ->orWhere('item_code_supplier', 'LIKE', $keyword)
                    ->orWhere('name', 'LIKE', $keyword)
                    ->orWhere('description', 'LIKE', $keyword)
                    ->orWhere('incoterms', 'LIKE', $keyword)
                    ->orWhereHas('vendor', fn ($v) => $v->where('name', 'LIKE', $keyword));
            });
        }

        return $query;
    }

public function locations()
{
    return $this->belongsToMany(Location::class, 'location_stock', 'item_id', 'location_id')
        ->withPivot(['opening_balance', 'lot', 'quantity'])
        ->withTimestamps();
}

    public function mutations()
    {
        return $this->hasMany(StockMutation::class);
    }

    public function locationStocks()
    {
        return $this->hasMany(LocationStock::class, 'item_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}