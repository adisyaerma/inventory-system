<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Master material packing (kardus, bubble wrap, solasi, dst.)
        Schema::create('packing_materials', function (Blueprint $table) {
            $table->id();
            $table->string('kode')->unique();
            $table->string('nama');
            $table->string('satuan', 30);
            $table->decimal('harga', 15, 2)->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        // 1 SO = 1 bill. Dikunci lewat so_number (unik).
        Schema::create('packing_bills', function (Blueprint $table) {
            $table->id();
            $table->string('so_number')->unique();
            $table->string('customer')->nullable();
            $table->date('tanggal');
            $table->decimal('total', 15, 2)->default(0);
            $table->string('dibuat_oleh')->nullable();
            $table->string('diubah_oleh')->nullable();
            $table->timestamps();
        });

        // Rincian material. nama/satuan/harga/item_label disalin (snapshot)
        // supaya bill lama tidak berubah kalau master material diubah atau
        // baris staging out-nya sudah dipindah ke history.
        Schema::create('packing_bill_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_bill_id')->constrained('packing_bills')->cascadeOnDelete();
            $table->unsignedBigInteger('staging_out_id')->nullable()->index(); // kosong = berlaku untuk seluruh SO
            $table->string('item_label')->nullable();
            $table->foreignId('packing_material_id')->nullable()->constrained('packing_materials')->nullOnDelete();
            $table->string('nama_material');
            $table->string('satuan', 30);
            $table->decimal('qty', 12, 3);
            $table->decimal('harga', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_bill_lines');
        Schema::dropIfExists('packing_bills');
        Schema::dropIfExists('packing_materials');
    }
};