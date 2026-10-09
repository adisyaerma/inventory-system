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
        Schema::table('location_stock', function (Blueprint $table) {
            // Mutasi dengan id <= nilai ini = riwayat sebelum stock opname,
            // tidak ikut dihitung ke saldo. Default 0 = semua mutasi dihitung.
            $table->unsignedBigInteger('baseline_mutation_id')
                ->default(0)
                ->after('opening_balance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('location_stock', function (Blueprint $table) {
            $table->dropColumn('baseline_mutation_id');
        });
    }
};