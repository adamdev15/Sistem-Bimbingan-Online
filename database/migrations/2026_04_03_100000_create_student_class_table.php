<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('siswas')->cascadeOnDelete();
            $table->foreignId('jadwal_id')->constrained('jadwals')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'jadwal_id']);
        });

        if (Schema::hasTable('kehadirans')) {
            $pairs = DB::table('kehadirans')
                ->select('student_id', 'jadwal_id')
                ->distinct()
                ->get();

            foreach ($pairs as $row) {
                DB::table('student_class')->insertOrIgnore([
                    'student_id' => $row->student_id,
                    'jadwal_id' => $row->jadwal_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_class');
    }
};
