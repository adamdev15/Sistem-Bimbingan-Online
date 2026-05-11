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
        Schema::table('tutors', function (Blueprint $table) {
            $table->enum('jenis_tutor', ['parttime', 'fulltime'])->default('parttime')->after('status');
        });

        Schema::table('materi_les', function (Blueprint $table) {
            $table->dropColumn('biaya_tutor');
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->decimal('gaji', 14, 2)->default(0)->after('periode');
            $table->decimal('insentif_kehadiran', 14, 2)->default(0)->after('gaji');
            $table->decimal('bonus_lainnya', 14, 2)->default(0)->after('insentif_kehadiran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutors', function (Blueprint $table) {
            $table->dropColumn('jenis_tutor');
        });

        Schema::table('materi_les', function (Blueprint $table) {
            $table->decimal('biaya_tutor', 14, 2)->default(0);
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn(['gaji', 'insentif_kehadiran', 'bonus_lainnya']);
        });
    }
};
