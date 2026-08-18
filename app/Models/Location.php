<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_name',
        'location_code',
        'description',
        'status',
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'location_stock', 'location_id', 'item_id')
            ->withPivot('quantity')
            ->withTimestamps();
    }
}