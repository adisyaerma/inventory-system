<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StagingInHistoryDetail extends Model
{
    use HasFactory;

    public const EVENT_TYPES = [
        'created' => 'Data Dibuat',
        'updated' => 'Data Diubah',
        'moved_to_stock' => 'Dipindah ke Stok',
        'moved_to_staging_out' => 'Dipindah ke Staging Out',
        'deleted' => 'Dihapus',
        'bulk_deleted' => 'Dihapus (Massal)',
        'reset_by_import' => 'Direset oleh Import',
        'restored' => 'Dipulihkan',
    ];

    /**
     * Warna badge Bootstrap per jenis event, dipakai di view.
     */
    public const EVENT_COLORS = [
        'created' => 'primary',
        'updated' => 'info',
        'moved_to_stock' => 'success',
        'moved_to_staging_out' => 'success',
        'deleted' => 'danger',
        'bulk_deleted' => 'danger',
        'reset_by_import' => 'warning',
        'restored' => 'success',
    ];

    protected $fillable = [
        'staging_in_history_id',
        'event_type',
        'qty_before',
        'qty_change',
        'qty_after',
        'meta',
        'notes',
        'performed_by',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function history(): BelongsTo
    {
        return $this->belongsTo(StagingInHistory::class, 'staging_in_history_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'performed_by');
    }

    public function getEventLabelAttribute(): string
    {
        return self::EVENT_TYPES[$this->event_type] ?? $this->event_type;
    }

    public function getEventColorAttribute(): string
    {
        return self::EVENT_COLORS[$this->event_type] ?? 'secondary';
    }
}