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
        Schema::create('staging_out_histories', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('staging_out_id')->index();

            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->string('so_number')->nullable();
            $table->string('customer')->nullable();
            $table->string('line_item')->nullable();
            $table->unsignedInteger('initial_qty')->default(0);
            $table->date('delivery_instruction_date')->nullable();

            $table->date('picking_date')->nullable();
            $table->string('do_number')->nullable();
            $table->date('delivery_date')->nullable();

            $table->boolean('is_active')->default(true);

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });

        // ================= DETAIL =================
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

            $table->unsignedInteger('qty_before')->nullable();
            $table->unsignedInteger('qty_change')->nullable();
            $table->unsignedInteger('qty_after')->nullable();

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