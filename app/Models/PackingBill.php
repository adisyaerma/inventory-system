<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingBill extends Model
{
    protected $fillable = ['so_number', 'customer', 'tanggal', 'total', 'dibuat_oleh', 'diubah_oleh'];

    protected $casts = [
        'tanggal' => 'date',
        'total' => 'decimal:2',
    ];

    public function kolis()
    {
        return $this->hasMany(PackingKoli::class, 'packing_bill_id');
    }

    /** Semua baris material (semua koli + material bersama). */
    public function lines()
    {
        return $this->hasMany(PackingBillLine::class, 'packing_bill_id');
    }

    /** Material bersama semua koli (packing_koli_id kosong). */
    public function sharedLines()
    {
        return $this->hasMany(PackingBillLine::class, 'packing_bill_id')->whereNull('packing_koli_id');
    }
}