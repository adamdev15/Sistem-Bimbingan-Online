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
        Schema::table('materi_les', function (Blueprint $table) {
            if (!Schema::hasColumn('materi_les', 'biaya_spp')) {
                $table->decimal('biaya_spp', 15, 2)->default(0)->after('biaya_daftar');
            }
        });

        Schema::table('siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('siswas', 'cuti_sampai')) {
                $table->date('cuti_sampai')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materi_les', function (Blueprint $table) {
            $table->dropColumn('biaya_spp');
        });

        Schema::table('siswas', function (Blueprint $table) {
            $table->dropColumn('cuti_sampai');
        });
    }
};
