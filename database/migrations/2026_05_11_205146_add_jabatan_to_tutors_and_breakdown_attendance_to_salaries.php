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
            $table->enum('jabatan', ['Tutor', 'Admin'])->default('Tutor')->after('status');
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->integer('full')->default(0)->after('total_kehadiran');
            $table->integer('pagi_siang')->default(0)->after('full');
            $table->integer('siang_sore')->default(0)->after('pagi_siang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn(['full', 'pagi_siang', 'siang_sore']);
        });

        Schema::table('tutors', function (Blueprint $table) {
            $table->dropColumn('jabatan');
        });
    }
};
