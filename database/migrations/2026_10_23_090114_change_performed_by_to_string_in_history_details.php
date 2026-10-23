<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * performed_by sebelumnya foreignId ke users. Aplikasi tidak punya
     * login sendiri (login lewat Cloudflare Access), jadi kolom diganti
     * menjadi string untuk menyimpan email dari Cloudflare.
     *
     * Nilai lama (null / id user) dibuang karena tidak ada tabel users
     * yang dipakai untuk login.
     */
    private array $tables = [
        'staging_in_history_details',
        'staging_out_history_details',
    ];

    public function up(): void
    {
        foreach ($this->tables as $tableName) {
            if (! Schema::hasColumn($tableName, 'performed_by')) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['performed_by']);
                $table->dropColumn('performed_by');
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->string('performed_by')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn('performed_by');
            });

            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
    }
};