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
        Schema::create('stagings', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->nullable();
            $table->date('arrival_date')->nullable();
            $table->string('supplier_origin')->nullable();
            $table->string('item_owner')->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedInteger('qty')->default(0);
            $table->enum('location', [
                'Inbound shipment',
                'Temporary hold / repair 1',
                'Temporary hold / repair 2',
                'Temporary hold / repair 3',
            ])->nullable();
            $table->enum('incoterms', [
                'VHS',
                'DDP',
                'SMELTER',
                'NON FI',
                'FLUKE',
                'NORD-LOCK',
            ])->nullable();
            $table->text('notes')->nullable();
            $table->text('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stagings');
    }
};