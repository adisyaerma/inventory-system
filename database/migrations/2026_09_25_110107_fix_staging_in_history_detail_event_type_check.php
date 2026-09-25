<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Perbaikan: CHECK constraint 'staging_in_history_details_event_type_check'
 * di database belum menyertakan value 'restored', padahal sudah terdaftar
 * di App\Models\StagingInHistoryDetail::EVENT_TYPES. Akibatnya proses
 * "restore" gagal insert history detail dengan error 23514.
 *
 * Migration ini men-drop constraint lama lalu membuat ulang dengan daftar
 * value yang sinkron dengan EVENT_TYPES di model.
 */
return new class extends Migration
{
    /**
     * Daftar ini HARUS tetap sinkron dengan
     * App\Models\StagingInHistoryDetail::EVENT_TYPES
     */
    private array $allowedEventTypes = [
        'created',
        'updated',
        'moved_to_stock',
        'moved_to_staging_out',
        'deleted',
        'bulk_deleted',
        'reset_by_import',
        'restored',
    ];

    public function up(): void
    {
        $list = "'" . implode("','", $this->allowedEventTypes) . "'";

        DB::statement('
            ALTER TABLE staging_in_history_details
            DROP CONSTRAINT IF EXISTS staging_in_history_details_event_type_check
        ');

        DB::statement("
            ALTER TABLE staging_in_history_details
            ADD CONSTRAINT staging_in_history_details_event_type_check
            CHECK (event_type IN ({$list}))
        ");
    }

    public function down(): void
    {
        // Kembalikan ke versi lama (tanpa 'restored') jika perlu rollback.
        // Sesuaikan daftar ini dengan constraint asli sebelum migration ini,
        // jika diketahui daftarnya berbeda.
        $oldList = "'created','updated','moved_to_stock','moved_to_staging_out','deleted','bulk_deleted','reset_by_import'";

        DB::statement('
            ALTER TABLE staging_in_history_details
            DROP CONSTRAINT IF EXISTS staging_in_history_details_event_type_check
        ');

        DB::statement("
            ALTER TABLE staging_in_history_details
            ADD CONSTRAINT staging_in_history_details_event_type_check
            CHECK (event_type IN ({$oldList}))
        ");
    }
};