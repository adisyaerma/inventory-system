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
        Schema::create('stock_mutations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_id')
                ->constrained('stocks')
                ->cascadeOnDelete();

            $table->foreignId('location_id')
                ->constrained('locations')
                ->cascadeOnDelete();

            $table->date('transaction_date')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('transaction_number')->nullable();
            $table->text('description')->nullable();
            $table->decimal('qty_in', 15, 2)->default(0);
            $table->decimal('qty_out', 15, 2)->default(0);
            $table->decimal('qty_balance', 15, 2)->default(0);
            $table->string('warehouse')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('value', 18, 2)->default(0);
            $table->timestamps();
            $table->index('transaction_date');
            $table->index('transaction_number');
            $table->index(['stock_id', 'location_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_mutations');
    }
};
