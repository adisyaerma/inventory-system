<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackingKoli extends Model
{
    protected $table = 'packing_kolis';

    protected $fillable = ['packing_bill_id', 'no_koli'];

    public function bill()
    {
        return $this->belongsTo(PackingBill::class, 'packing_bill_id');
    }

    public function items()
    {
        return $this->hasMany(PackingKoliItem::class, 'packing_koli_id');
    }

    public function lines()
    {
        return $this->hasMany(PackingBillLine::class, 'packing_koli_id');
    }
}