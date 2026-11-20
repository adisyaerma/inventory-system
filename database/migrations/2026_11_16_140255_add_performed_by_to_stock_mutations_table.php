<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('stock_mutations', 'performed_by')) {
            return;
        }

        Schema::table('stock_mutations', function (Blueprint $table) {
            // Email user dari header Cloudflare Access
            // (Cf-Access-Authenticated-User-Email). Nullable karena data
            // lama tidak punya info ini.
            $table->string('performed_by')->nullable()->after('lot');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('stock_mutations', 'performed_by')) {
            return;
        }

        Schema::table('stock_mutations', function (Blueprint $table) {
            $table->dropColumn('performed_by');
        });
    }
};