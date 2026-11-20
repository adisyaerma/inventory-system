<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingBillLine extends Model
{
    protected $fillable = [
        'packing_bill_id', 'packing_koli_id', 'packing_material_id',
        'nama_material', 'satuan', 'qty', 'harga', 'subtotal', 'sort_order',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'harga' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function bill()
    {
        return $this->belongsTo(PackingBill::class, 'packing_bill_id');
    }

    public function koli()
    {
        return $this->belongsTo(PackingKoli::class, 'packing_koli_id');
    }

    public function material()
    {
        return $this->belongsTo(PackingMaterial::class, 'packing_material_id');
    }
}