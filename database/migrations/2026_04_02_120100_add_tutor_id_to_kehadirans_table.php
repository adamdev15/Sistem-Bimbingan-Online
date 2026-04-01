<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kehadirans', function (Blueprint $table) {
            $table->foreignId('tutor_id')->nullable()->after('jadwal_id')->constrained('tutors')->cascadeOnUpdate()->restrictOnDelete();
        });

        foreach (DB::table('kehadirans')->select('id', 'jadwal_id')->get() as $row) {
            $tid = DB::table('jadwals')->where('id', $row->jadwal_id)->value('tutor_id');
            if ($tid) {
                DB::table('kehadirans')->where('id', $row->id)->update(['tutor_id' => $tid]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('kehadirans', function (Blueprint $table) {
            $table->dropForeign(['tutor_id']);
            $table->dropColumn('tutor_id');
        });
    }
};
