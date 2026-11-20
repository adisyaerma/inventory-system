<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nomor koli (kardus) tempat material dipakai. Bill lama otomatis jadi koli 1.
        Schema::table('packing_bill_lines', function (Blueprint $table) {
            $table->unsignedSmallInteger('koli_no')->default(1)->after('packing_bill_id');
        });
    }

    public function down(): void
    {
        Schema::table('packing_bill_lines', function (Blueprint $table) {
            $table->dropColumn('koli_no');
        });
    }
};