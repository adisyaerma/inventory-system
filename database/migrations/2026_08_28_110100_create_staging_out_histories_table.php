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
        // 1 baris per "siklus hidup" staging_out (dibuat sekali saat data
        // staging_out pertama kali dibuat, lalu dipakai terus sepanjang
        // umur data itu meskipun baris staging_out aslinya nanti berubah
        // atau bahkan dihapus).
        //
        // Beda dengan staging_in: staging_out TIDAK pernah "pindah" ke
        // mana-mana (tidak ada moved_to_stock / moved_to_staging_out).
        // Siklusnya linear: dibuat -> picking dikonfirmasi -> dikirim
        // (DO terbit). Karena itu picking_date, do_number, dan
        // delivery_date disimpan langsung di header sebagai kolom
        // "status terkini" (disinkronkan tiap event terjadi), bukan
        // cuma snapshot awal — supaya panel detail bisa baca status
        // pengiriman langsung tanpa harus hitung ulang dari timeline.
        Schema::create('staging_out_histories', function (Blueprint $table) {
            $table->id();

            // Referensi ke staging_outs TANPA foreign key constraint.
            // Sengaja dibiarkan sebagai kolom biasa karena baris
            // staging_out bisa benar-benar hilang (dihapus manual /
            // di-reset oleh import) — kalau pakai FK + nullOnDelete,
            // link ini justru akan hilang di saat paling penting untuk
            // dicatat.
            $table->unsignedBigInteger('staging_out_id')->index();

            // Snapshot identitas saat pertama kali dibuat, supaya history
            // tetap terbaca walau baris staging_out aslinya sudah tidak ada.
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('so_number')->nullable();
            $table->string('customer')->nullable();
            $table->string('line_item')->nullable();
            $table->unsignedInteger('initial_qty')->default(0);
            $table->date('delivery_instruction_date')->nullable();

            // Kolom status terkini — diisi/diupdate oleh
            // StagingOutHistoryService setiap kali event picking_confirmed
            // atau delivered terjadi (atau saat field ini diubah lewat
            // event updated).
            $table->date('picking_date')->nullable();
            $table->string('do_number')->nullable();
            $table->date('delivery_date')->nullable();

            // true selama baris staging_out aslinya masih ada.
            // Diset false begitu baris staging_out dihapus (dihapus
            // manual, bulk delete, atau reset import). TIDAK ada
            // hubungannya dengan status pengiriman — staging_out yang
            // sudah delivered pun tetap is_active = true selama baris
            // aslinya masih ada di tabel staging_outs.
            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });

        // ================= DETAIL =================
        // Setiap kejadian / perubahan pada staging_out dicatat di sini.
        Schema::create('staging_out_history_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('staging_out_history_id')
                ->constrained('staging_out_histories')
                ->cascadeOnDelete();

            $table->enum('event_type', [
                'created',
                'updated',
                'picking_confirmed',
                'delivered',
                'deleted',
                'bulk_deleted',
                'reset_by_import',
            ]);

            // Relevan terutama untuk event 'created' & 'updated' (mis. qty
            // dikoreksi). Untuk picking_confirmed / delivered biasanya null
            // karena event ini tidak mengubah qty, murni menandai tanggal.
            $table->unsignedInteger('qty_before')->nullable();
            $table->unsignedInteger('qty_change')->nullable();
            $table->unsignedInteger('qty_after')->nullable();

            // Detail tambahan sesuai jenis event, contoh:
            // - updated            : {"customer":{"old":"PT A","new":"PT B"}}
            // - picking_confirmed  : {"picking_date":"2026-08-20"}
            // - delivered          : {"do_number":"DO-001","delivery_date":"2026-08-25"}
            $table->json('meta')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['staging_out_history_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staging_out_history_details');
        Schema::dropIfExists('staging_out_histories');
    }
};