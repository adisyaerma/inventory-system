<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staging_outs', function (Blueprint $table) {
            // Posisi fisik barang setelah dipicking. Hanya diisi/diubah dari
            // select langsung di baris tabel, bukan lewat modal tambah/edit,
            // dan hanya relevan setelah picking_date terisi.
            $table->enum('staging_location', ['staging', 'packing', 'outbound'])
                ->nullable()
                ->after('picking_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staging_outs', function (Blueprint $table) {
            $table->dropColumn('staging_location');
        });
    }
};