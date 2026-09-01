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
        // ================= HEADER =================
        // 1 baris per "siklus hidup" staging_in (dibuat sekali saat data
        // staging_in pertama kali dibuat, lalu dipakai terus sepanjang
        // umur data itu meskipun baris staging_in aslinya nanti berubah
        // atau bahkan dihapus).
        Schema::create('staging_in_histories', function (Blueprint $table) {
            $table->id();

            // Referensi ke staging_ins TANPA foreign key constraint.
            // Sengaja dibiarkan sebagai kolom biasa karena baris staging_in
            // bisa benar-benar hilang (habis qty-nya / dihapus manual /
            // di-reset oleh import) — kalau pakai FK + nullOnDelete, link
            // ini justru akan hilang di saat paling penting untuk dicatat.
            $table->unsignedBigInteger('staging_in_id')->index();

            // Snapshot identitas saat pertama kali dibuat, supaya history
            // tetap terbaca walau baris staging_in aslinya sudah tidak ada.
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('po_number')->nullable();
            $table->string('supplier_origin')->nullable();
            $table->date('arrival_date')->nullable();
            $table->string('incoterms')->nullable();
            $table->string('location')->nullable();
            $table->unsignedInteger('initial_qty')->default(0);

            // true selama baris staging_in aslinya masih ada / masih aktif.
            // Diset false begitu baris staging_in dihapus (habis dipindah,
            // dihapus manual, bulk delete, atau reset import).
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });

        // ================= DETAIL =================
        // Setiap kejadian / perubahan pada staging_in dicatat di sini.
        Schema::create('staging_in_history_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('staging_in_history_id')
                ->constrained('staging_in_histories')
                ->cascadeOnDelete();

            $table->enum('event_type', [
                'created',
                'updated',
                'moved_to_stock',
                'moved_to_staging_out',
                'deleted',
                'bulk_deleted',
                'reset_by_import',
            ]);

            $table->unsignedInteger('qty_before')->nullable();
            $table->unsignedInteger('qty_change')->nullable(); // qty yg dipindah/dikurangi pada event ini
            $table->unsignedInteger('qty_after')->nullable();

            // Detail tambahan sesuai jenis event, contoh:
            // - updated             : {"location":{"old":"A","new":"B"}}
            // - moved_to_stock      : {"location":"Rak A1","transaction_number":"TRX-001"}
            // - moved_to_staging_out: {"so_number":"SO-001","customer":"PT ABC","line_item":"1"}
            $table->json('meta')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['staging_in_history_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staging_in_history_details');
        Schema::dropIfExists('staging_in_histories');
    }
};