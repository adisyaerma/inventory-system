<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StagingOutHistoryDetail extends Model
{
    use HasFactory;

    public const EVENT_TYPES = [
        'created' => 'Data Dibuat',
        'updated' => 'Data Diubah',
        'picking_confirmed' => 'Picking Dikonfirmasi',
        'delivered' => 'Sudah Dikirim',
        'deleted' => 'Dihapus',
        'bulk_deleted' => 'Dihapus (Massal)',
        'reset_by_import' => 'Direset oleh Import',
    ];

    /**
     * Warna badge Bootstrap per jenis event, dipakai di view.
     */
    public const EVENT_COLORS = [
        'created' => 'primary',
        'updated' => 'info',
        'picking_confirmed' => 'warning',
        'delivered' => 'success',
        'deleted' => 'danger',
        'bulk_deleted' => 'danger',
        'reset_by_import' => 'warning',
    ];

    protected $fillable = [
        'staging_out_history_id',
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
        return $this->belongsTo(StagingOutHistory::class, 'staging_out_history_id');
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