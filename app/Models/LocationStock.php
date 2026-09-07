<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationStock extends Model
{
    use HasFactory;

    protected $table = 'location_stock';

    protected $fillable = [
        'item_id',
        'location_id',
        'opening_balance',
        'quantity',
        'lot',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

}
