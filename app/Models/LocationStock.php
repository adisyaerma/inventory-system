<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationStock extends Model
{
    use HasFactory;

    protected $table = 'location_stock';

    protected $fillable = [
        'stock_id',
        'location_id',
        'quantity',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }
}
