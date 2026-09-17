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
            // Tanggal resi pengiriman (bukti terima barang dari customer) —
            // berbeda dengan delivery_date (tanggal barang dikirim/dijadwalkan
            // kirim). Ditaruh setelah delivery_date karena secara alur, resi
            // baru ada/diterima setelah pengiriman dilakukan.
            $table->date('delivery_receipt_date')->nullable()->after('delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staging_outs', function (Blueprint $table) {
            $table->dropColumn('delivery_receipt_date');
        });
    }
};