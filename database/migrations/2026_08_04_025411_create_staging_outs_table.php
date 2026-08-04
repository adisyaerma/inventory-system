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
        Schema::create('staging_outs', function (Blueprint $table) {
            $table->id();
            $table->string('so_number')->nullable();
            $table->string('customer')->nullable();
            $table->string('item_code')->nullable();
            $table->string('line_item')->nullable();
            $table->integer('qty')->nullable();
            $table->date('delivery_instruction_date')->nullable();
            $table->date('picking_date')->nullable();
            $table->string('do_number')->nullable();
            $table->date('delivery_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staging_outs');
    }
};
