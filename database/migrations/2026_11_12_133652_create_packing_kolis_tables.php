<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Koli (kardus) milik sebuah bill.
        Schema::create('packing_kolis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_bill_id')->constrained('packing_bills')->cascadeOnDelete();
            $table->unsignedSmallInteger('no_koli');
            $table->timestamps();
        });

        // Isi koli: item mana saja yang ada di koli itu. Kosong = semua item di SO
        // (dipakai kalau SO hanya 1 koli).
        Schema::create('packing_koli_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('packing_koli_id')->constrained('packing_kolis')->cascadeOnDelete();
            $table->unsignedBigInteger('staging_out_id')->nullable()->index();
            $table->string('item_label')->nullable(); // salinan, supaya tetap terbaca setelah barang pindah ke history
            $table->timestamps();
        });

        // Material sekarang menempel ke koli. packing_koli_id kosong = material bersama semua koli.
        Schema::table('packing_bill_lines', function (Blueprint $table) {
            $table->foreignId('packing_koli_id')->nullable()->after('packing_bill_id')
                ->constrained('packing_kolis')->cascadeOnDelete();
        });

        // ---- Pindahkan data bill lama ke struktur koli ----
        $hasKoliNo = Schema::hasColumn('packing_bill_lines', 'koli_no');
        $now = now();

        $byBill = DB::table('packing_bill_lines')
            ->orderBy('packing_bill_id')->orderBy('sort_order')->orderBy('id')
            ->get()->groupBy('packing_bill_id');

        foreach ($byBill as $billId => $billLines) {
            $byKoli = $billLines->groupBy(fn ($l) => $hasKoliNo ? ($l->koli_no ?: 1) : 1)->sortKeys();
            $multi = $byKoli->count() > 1;
            $n = 0;

            foreach ($byKoli as $kl) {
                $koliId = DB::table('packing_kolis')->insertGetId([
                    'packing_bill_id' => $billId,
                    'no_koli' => ++$n,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                // Bill 1 koli: isi koli = semua item (tidak perlu dicatat).
                if ($multi) {
                    foreach ($kl->filter(fn ($l) => $l->staging_out_id)->unique('staging_out_id') as $l) {
                        DB::table('packing_koli_items')->insert([
                            'packing_koli_id' => $koliId,
                            'staging_out_id' => $l->staging_out_id,
                            'item_label' => $l->item_label,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }

                DB::table('packing_bill_lines')->whereIn('id', $kl->pluck('id'))->update(['packing_koli_id' => $koliId]);
            }
        }

        // ---- Kolom lama tidak dipakai lagi ----
        if (Schema::hasColumn('packing_bill_lines', 'staging_out_id')) {
            Schema::table('packing_bill_lines', function (Blueprint $table) {
                $table->dropIndex(['staging_out_id']);
            });
        }

        Schema::table('packing_bill_lines', function (Blueprint $table) {
            $drop = array_values(array_filter(
                ['koli_no', 'staging_out_id', 'item_label'],
                fn ($c) => Schema::hasColumn('packing_bill_lines', $c)
            ));

            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }

    public function down(): void
    {
        Schema::table('packing_bill_lines', function (Blueprint $table) {
            $table->unsignedBigInteger('staging_out_id')->nullable()->index();
            $table->string('item_label')->nullable();
            $table->unsignedSmallInteger('koli_no')->default(1);
        });

        Schema::table('packing_bill_lines', function (Blueprint $table) {
            $table->dropConstrainedForeignId('packing_koli_id');
        });

        Schema::dropIfExists('packing_koli_items');
        Schema::dropIfExists('packing_kolis');
    }
};