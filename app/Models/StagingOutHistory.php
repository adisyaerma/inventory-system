<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 1 baris per "siklus hidup" staging_out. Header ini menyimpan snapshot
 * identitas awal (item, so_number, customer, dll) plus kolom status
 * terkini (picking_date, do_number, delivery_date) yang disinkronkan
 * oleh StagingOutHistoryService setiap kali event terjadi — lihat
 * komentar di migration create_staging_out_histories_table.
 */
class StagingOutHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'staging_out_id',
        'item_id',
        'so_number',
        'customer',
        'line_item',
        'initial_qty',
        'delivery_instruction_date',
        'picking_date',
        'do_number',
        'delivery_date',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'initial_qty' => 'integer',
        'delivery_instruction_date' => 'date',
        'picking_date' => 'date',
        'delivery_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Detail kejadian, diurutkan terbaru dulu (dipakai untuk ambil "last
     * event" dengan cepat). Untuk timeline kronologis, reorder() dulu
     * seperti dilakukan di StagingOutHistoryController::detail().
     */
    public function details()
    {
        return $this->hasMany(StagingOutHistoryDetail::class)->latest();
    }

    /**
     * Ambil (atau buat kalau belum ada) header history untuk satu baris
     * staging_out. Dipanggil oleh StagingOutHistoryService di setiap
     * method log*() supaya history selalu punya header untuk ditempeli
     * detail, tanpa peduli apakah ini kejadian pertama kali atau bukan.
     *
     * Snapshot identitas (item_id, so_number, dst) hanya diisi SEKALI saat
     * baris header ini pertama kali dibuat — panggilan berikutnya tidak
     * menimpa snapshot itu, hanya mengembalikan header yang sudah ada.
     */
    public static function firstOrCreateForStaging(StagingOut $staging): self
    {
        return static::firstOrCreate(
            ['staging_out_id' => $staging->id],
            [
                'item_id' => $staging->item_id,
                'so_number' => $staging->so_number,
                'customer' => $staging->customer,
                'line_item' => $staging->line_item,
                'initial_qty' => $staging->qty,
                'delivery_instruction_date' => $staging->delivery_instruction_date,
                'picking_date' => $staging->picking_date,
                'do_number' => $staging->do_number,
                'delivery_date' => $staging->delivery_date,
                'is_active' => true,
                'created_by' => auth()->id(),
            ]
        );
    }
}