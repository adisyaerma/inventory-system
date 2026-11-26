<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'source_type',
        'location_id',
        'lot',
        'delivery_receipt_date',
        'staging_location',
        'note',
    ];

    protected $casts = [
        'delivery_instruction_date' => 'date',
        'picking_date' => 'date',
        'delivery_date' => 'date',
        'delivery_receipt_date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $row) {
            $row->group_key ??= (string) Str::uuid();
        });

        static::created(function (self $row) {
            StagingOutHistory::where('staging_out_id', $row->id)
                ->update(['staging_out_id' => null, 'is_active' => false]);
        });
    }
}
