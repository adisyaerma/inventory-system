<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Pilihan: VHS, FLUKE, NORD LOCK (divalidasi di aplikasi lewat Item::INCOTERMS)
            $table->string('incoterms', 50)->nullable()->after('vendor_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['incoterms']);
            $table->dropColumn('incoterms');
        });
    }
};