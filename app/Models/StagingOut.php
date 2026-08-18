<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StagingOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'so_number',
        'customer',
        'item_id',
        'line_item',
        'qty',
        'delivery_instruction_date',
        'picking_date',
        'do_number',
        'delivery_date',
    ];

    protected $casts = [
        'delivery_instruction_date' => 'date',
        'picking_date' => 'date',
        'delivery_date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
