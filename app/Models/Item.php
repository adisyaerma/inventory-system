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
        'quantity',
    ];

public function locations()
{
    return $this->belongsToMany(Location::class, 'location_stock', 'item_id', 'location_id')
        ->withPivot(['opening_balance', 'quantity'])
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
