<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staging_outs', function (Blueprint $table) {
            // Asal baris ini kalau dibuat lewat "Pindahkan ke Staging Out".
            // Sengaja TANPA foreign key: baris staging in bisa terhapus saat
            // qty-nya habis dipindah, dan nanti dibuat ulang dari snapshot.
            $table->unsignedBigInteger('staging_in_id')->nullable()->after('id')->index();
            $table->json('staging_in_snapshot')->nullable()->after('staging_in_id');
        });
    }

    public function down(): void
    {
        Schema::table('staging_outs', function (Blueprint $table) {
            $table->dropColumn(['staging_in_id', 'staging_in_snapshot']);
        });
    }
};