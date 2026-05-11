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
        Schema::table('siswas', function (Blueprint $table) {
            $table->enum('registration_type', ['baru', 'lama'])->default('baru')->after('status');
        });

        Schema::create('branch_materi_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cabang_id')->constrained()->onDelete('cascade');
            $table->foreignId('materi_les_id')->constrained('materi_les')->onDelete('cascade');
            $table->decimal('biaya_daftar', 12, 2)->default(0);
            $table->decimal('biaya_spp', 12, 2)->default(0);
            $table->timestamps();
        });

        // Copy existing global prices to all branches as a starting point
        $materiLes = DB::table('materi_les')->get();
        $cabangs = DB::table('cabangs')->get();

        foreach ($cabangs as $cabang) {
            foreach ($materiLes as $materi) {
                DB::table('branch_materi_prices')->insert([
                    'cabang_id' => $cabang->id,
                    'materi_les_id' => $materi->id,
                    'biaya_daftar' => $materi->biaya_daftar ?? 0,
                    'biaya_spp' => $materi->biaya_spp ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('materi_les', function (Blueprint $table) {
            $table->dropColumn(['biaya_daftar', 'biaya_spp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materi_les', function (Blueprint $table) {
            $table->decimal('biaya_daftar', 12, 2)->default(0);
            $table->decimal('biaya_spp', 12, 2)->default(0);
        });

        Schema::dropIfExists('branch_materi_prices');

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('registration_type');
        });
    }
};
