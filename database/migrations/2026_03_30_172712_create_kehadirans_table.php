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
        Schema::create('kehadirans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('siswas');
            $table->foreignId('jadwal_id')->constrained('jadwals');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'alfa'])->default('hadir');
            $table->timestamps();
            $table->unique(['student_id', 'jadwal_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadirans');
    }
};
