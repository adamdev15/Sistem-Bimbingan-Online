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
        if (Schema::hasTable('kehadirans')) {
            Schema::rename('kehadirans', 'kehadiran_siswas');
        }

        Schema::table('kehadiran_siswas', function (Blueprint $table) {
            $table->dropForeign('kehadirans_tutor_id_foreign');
            $table->dropColumn('tutor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kehadiran_siswas', function (Blueprint $table) {
            $table->unsignedBigInteger('tutor_id')->nullable()->after('student_id');
            $table->foreign('tutor_id')->references('id')->on('tutors')->onDelete('set null');
        });

        Schema::rename('kehadiran_siswas', 'kehadirans');
    }
};
