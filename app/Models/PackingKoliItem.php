<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingKoliItem extends Model
{
    protected $fillable = ['packing_koli_id', 'staging_out_id', 'item_label'];

    public function koli()
    {
        return $this->belongsTo(PackingKoli::class, 'packing_koli_id');
    }
}