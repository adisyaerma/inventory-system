<?php

namespace App\Models;

use App\Support\CurrentUser;
use Illuminate\Database\Eloquent\Model;

class StockMutation extends Model
{
    protected $fillable = [
        'item_id',
        'location_id',
        'transaction_date',
        'transaction_type',
        'transaction_number',
        'description',
        'qty_in',
        'qty_out',
        'qty_balance',
        'warehouse',
        'reference',
        'value',
        'lot',
        'performed_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
    ];

    /**
     * Setiap mutasi baru otomatis mencatat siapa yang membuatnya (email dari
     * header Cloudflare Access), dari jalur mana pun mutasi itu dibuat:
     * form tambah, import, pindah lokasi, staging, stock opname, dll.
     * Hanya saat "creating", supaya recalculate saldo (update qty_balance)
     * tidak menimpa nama pembuatnya.
     */
    protected static function booted(): void
    {
        static::creating(function (StockMutation $mutation) {
            if (empty($mutation->performed_by)) {
                $mutation->performed_by = CurrentUser::email();
            }
        });
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}