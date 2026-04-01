<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mata_pelajarans')) {
            Schema::create('mata_pelajarans', function (Blueprint $table) {
                $table->id();
                $table->string('nama');
                $table->string('kode')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('jadwals') || ! Schema::hasColumn('jadwals', 'mapel')) {
            return;
        }

        Schema::table('jadwals', function (Blueprint $table) {
            $table->foreignId('mata_pelajaran_id')->nullable()->after('cabang_id')->constrained('mata_pelajarans')->cascadeOnUpdate()->restrictOnDelete();
        });

        $names = DB::table('jadwals')->distinct()->pluck('mapel')->filter();

        foreach ($names as $nama) {
            if ($nama === null || $nama === '') {
                continue;
            }
            if (! DB::table('mata_pelajarans')->where('nama', $nama)->exists()) {
                DB::table('mata_pelajarans')->insert([
                    'nama' => $nama,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (! DB::table('mata_pelajarans')->where('nama', 'Umum')->exists()) {
            DB::table('mata_pelajarans')->insert([
                'nama' => 'Umum',
                'kode' => 'UMUM',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $umumId = (int) DB::table('mata_pelajarans')->where('nama', 'Umum')->value('id');
        $idByNama = DB::table('mata_pelajarans')->pluck('id', 'nama')->all();

        foreach (DB::table('jadwals')->select('id', 'mapel')->get() as $row) {
            $key = $row->mapel;
            $mid = ($key !== null && $key !== '' && isset($idByNama[$key])) ? $idByNama[$key] : $umumId;
            DB::table('jadwals')->where('id', $row->id)->update(['mata_pelajaran_id' => $mid]);
        }

        Schema::table('jadwals', function (Blueprint $table) {
            $table->dropColumn('mapel');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('jadwals') && ! Schema::hasColumn('jadwals', 'mapel')) {
            Schema::table('jadwals', function (Blueprint $table) {
                $table->string('mapel')->nullable()->after('cabang_id');
            });

            if (Schema::hasColumn('jadwals', 'mata_pelajaran_id')) {
                $rows = DB::table('jadwals')
                    ->leftJoin('mata_pelajarans', 'mata_pelajarans.id', '=', 'jadwals.mata_pelajaran_id')
                    ->select('jadwals.id', 'mata_pelajarans.nama as nama_mapel')
                    ->get();

                foreach ($rows as $row) {
                    DB::table('jadwals')->where('id', $row->id)->update([
                        'mapel' => $row->nama_mapel ?? 'Umum',
                    ]);
                }

                Schema::table('jadwals', function (Blueprint $table) {
                    $table->dropForeign(['mata_pelajaran_id']);
                    $table->dropColumn('mata_pelajaran_id');
                });
            }
        }

        Schema::dropIfExists('mata_pelajarans');
    }
};
