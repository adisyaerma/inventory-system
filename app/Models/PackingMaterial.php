<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingMaterial extends Model
{
    protected $fillable = ['kode', 'nama', 'satuan', 'harga', 'aktif'];

    protected $casts = [
        'harga' => 'decimal:2',
        'aktif' => 'boolean',
    ];

    public function lines()
    {
        return $this->hasMany(PackingBillLine::class);
    }
}