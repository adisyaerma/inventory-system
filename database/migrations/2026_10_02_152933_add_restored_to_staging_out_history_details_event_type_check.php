<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TABLE = 'staging_out_history_details';

    private const CONSTRAINT = 'staging_out_history_details_event_type_check';

    private const KNOWN = [
        'created',
        'updated',
        'picking_confirmed',
        'delivered',
        'deleted',
        'bulk_deleted',
        'reset_by_import',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $existing = DB::table(self::TABLE)->distinct()->pluck('event_type')->all();

        $this->replaceConstraint(array_merge(self::KNOWN, $existing, ['restored']));
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        if (DB::table(self::TABLE)->where('event_type', 'restored')->exists()) {
            throw new RuntimeException(
                "Tidak bisa rollback: masih ada event 'restored' di ".self::TABLE.'.'
            );
        }

        $existing = DB::table(self::TABLE)->distinct()->pluck('event_type')->all();

        $this->replaceConstraint(array_merge(self::KNOWN, $existing));
    }

    private function replaceConstraint(array $values): void
    {
        $pdo = DB::getPdo();

        $list = implode(', ', array_map(
            fn ($value) => $pdo->quote($value),
            array_values(array_unique($values))
        ));

        DB::statement('ALTER TABLE '.self::TABLE.' DROP CONSTRAINT IF EXISTS '.self::CONSTRAINT);
        DB::statement('ALTER TABLE '.self::TABLE.' ADD CONSTRAINT '.self::CONSTRAINT." CHECK (event_type IN ({$list}))");
    }
};