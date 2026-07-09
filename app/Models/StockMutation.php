<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    protected $fillable = [
        'stock_id',
        'location_id',
        'transaction_date',
        'transaction_type',
        'transaction_number',
        'description',
        'qty_in',
        'qty_out',
        'qty_balance',
        'warehouse',
        'reference',
        'value',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
