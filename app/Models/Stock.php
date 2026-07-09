<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_code_internal',
        'item_code_supplier',
        'item_code_customer',
        'name',
        'description',
        'location_id',
        'quantity',
    ];

    public function locations()
    {
        return $this->belongsToMany(Location::class)
            ->withPivot('quantity')
            ->withTimestamps();
    }

    public function mutations()
    {
        return $this->hasMany(StockMutation::class);
    }

    public function locationStocks()
    {
        return $this->hasMany(LocationStock::class, 'stock_id');
    }
}
