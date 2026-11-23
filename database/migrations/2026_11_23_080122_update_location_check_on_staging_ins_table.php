<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\StagingIn;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('ALTER TABLE staging_ins DROP CONSTRAINT IF EXISTS staging_ins_location_check');

        $list = collect(StagingIn::LOCATIONS)
            ->map(fn ($l) => "'" . str_replace("'", "''", $l) . "'")
            ->implode(', ');

        DB::statement("ALTER TABLE staging_ins ADD CONSTRAINT staging_ins_location_check CHECK (location IS NULL OR location::text IN ($list))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE staging_ins DROP CONSTRAINT IF EXISTS staging_ins_location_check');
    }
};