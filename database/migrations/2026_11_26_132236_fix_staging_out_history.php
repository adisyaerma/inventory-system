<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        // History lama boleh dilepas dari ID
        Schema::table('staging_out_histories', function (Blueprint $table) {
            $table->unsignedBigInteger('staging_out_id')->nullable()->change();
        });

        // Kolom kode grup, sekaligus diisi untuk data lama
        foreach (['staging_outs', 'staging_out_histories'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->uuid('group_key')->nullable()->index();
            });

            foreach (DB::table($name)->pluck('id') as $id) {
                DB::table($name)->where('id', $id)->update(['group_key' => (string) Str::uuid()]);
            }
        }

        // Majukan penghitung ID melewati semua ID yang pernah dipakai
        DB::statement("
            SELECT setval(
                pg_get_serial_sequence('staging_outs', 'id'),
                GREATEST(
                    (SELECT COALESCE(MAX(id), 0) FROM staging_outs),
                    (SELECT COALESCE(MAX(staging_out_id), 0) FROM staging_out_histories)
                ) + 1,
                false
            )
        ");
    }

    public function down(): void
    {
        foreach (['staging_outs', 'staging_out_histories'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('group_key'));
        }
    }
};