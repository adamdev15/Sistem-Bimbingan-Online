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
        // 1. ALTER materi_les
        Schema::table('materi_les', function (Blueprint $table) {
            if (!Schema::hasColumn('materi_les', 'foto')) $table->string('foto')->nullable();
            if (!Schema::hasColumn('materi_les', 'pertemuan_per_minggu')) $table->integer('pertemuan_per_minggu')->default(3);
            if (!Schema::hasColumn('materi_les', 'fee_id')) $table->foreignId('fee_id')->nullable()->constrained('fees')->nullOnDelete();
            if (!Schema::hasColumn('materi_les', 'biaya_daftar')) $table->decimal('biaya_daftar', 15, 2)->default(0);
        });

        // 2. ALTER cabangs
        Schema::table('cabangs', function (Blueprint $table) {
            if (!Schema::hasColumn('cabangs', 'sistem_hasil')) $table->enum('sistem_hasil', ['bagi_hasil', 'pusat'])->default('pusat');
            if (!Schema::hasColumn('cabangs', 'profit_share_investor')) $table->decimal('profit_share_investor', 5, 2)->default(0);
            if (!Schema::hasColumn('cabangs', 'profit_share_pusat')) $table->decimal('profit_share_pusat', 5, 2)->default(0);
        });

        // 3. ALTER kehadirans
        $indexesArray = Schema::getIndexes('kehadirans');
        $indexes = [];
        foreach($indexesArray as $idx) { $indexes[$idx['name']] = true; }
        
        $fksArray = Schema::getForeignKeys('kehadirans');
        $fks = [];
        foreach($fksArray as $fk) { $fks[$fk['name']] = true; }
        
        Schema::table('kehadirans', function (Blueprint $table) use ($indexes, $fks) {
            if (array_key_exists('kehadirans_student_id_foreign', $fks)) {
                $table->dropForeign(['student_id']);
            }
            if (array_key_exists('kehadirans_student_id_jadwal_id_tanggal_unique', $indexes)) {
                $table->dropUnique('kehadirans_student_id_jadwal_id_tanggal_unique');
            }
            if (array_key_exists('kehadirans_student_id_foreign', $fks)) {
                $table->foreign('student_id')->references('id')->on('siswas');
            }
            if (array_key_exists('kehadirans_jadwal_id_foreign', $fks)) {
                $table->dropForeign(['jadwal_id']);
            }
            if (array_key_exists('kehadirans_jadwal_id_foreign', $indexes)) {
                try { $table->dropIndex('kehadirans_jadwal_id_foreign'); } catch(\Exception $e) {}
            }
            
            if (Schema::hasColumn('kehadirans', 'jadwal_id')) {
                $table->dropColumn('jadwal_id');
            }
            if (Schema::hasColumn('kehadirans', 'mata_pelajaran_id')) {
                try { $table->dropForeign(['mata_pelajaran_id']); } catch (\Exception $e) {}
                $table->dropColumn('mata_pelajaran_id');
            }
            
            if (!Schema::hasColumn('kehadirans', 'materi_les_id')) {
                $table->foreignId('materi_les_id')->nullable()->constrained('materi_les')->cascadeOnUpdate()->nullOnDelete();
            }
            if (!Schema::hasColumn('kehadirans', 'cabang_id')) {
                $table->foreignId('cabang_id')->nullable()->constrained('cabangs')->cascadeOnUpdate()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('kehadirans', 'jam_mulai')) $table->time('jam_mulai')->nullable();
            if (!Schema::hasColumn('kehadirans', 'jam_selesai')) $table->time('jam_selesai')->nullable();
            if (!Schema::hasColumn('kehadirans', 'catatan')) $table->text('catatan')->nullable();
        });

        // 4. DROP student_class, jadwals, mata_pelajarans (Order matters for foreign keys)
        Schema::dropIfExists('student_class');
        Schema::dropIfExists('jadwals');
        Schema::dropIfExists('mata_pelajarans');

        // 5. ALTER siswas
        Schema::table('siswas', function (Blueprint $table) {
            if (!Schema::hasColumn('siswas', 'tanggal_cuti')) $table->date('tanggal_cuti')->nullable();
            if (!Schema::hasColumn('siswas', 'tanggal_selesai_cuti')) $table->date('tanggal_selesai_cuti')->nullable();
        });

        // 6. ALTER payments
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'tanggal_jatuh_tempo')) $table->date('tanggal_jatuh_tempo')->nullable();
            if (!Schema::hasColumn('payments', 'catatan')) $table->text('catatan')->nullable();
        });

        // 7. ALTER salaries
        Schema::table('salaries', function (Blueprint $table) {
            if (Schema::hasColumn('salaries', 'total_jam')) {
                $table->dropColumn('total_jam');
            }
            if (!Schema::hasColumn('salaries', 'total_kehadiran')) $table->integer('total_kehadiran')->default(0);
            if (!Schema::hasColumn('salaries', 'catatan')) $table->text('catatan')->nullable();
        });

        // 8. CREATE pengeluarans
        if (!Schema::hasTable('pengeluarans')) {
            Schema::create('pengeluarans', function (Blueprint $table) {
                $table->id();
                $table->date('tanggal');
                $table->enum('tipe', ['pemasukan', 'pengeluaran']);
                $table->decimal('nominal', 15, 2);
                $table->text('keterangan');
                $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // As this is a major architectural rewrite and data might be lost in up(), down() will mostly reverse structures.
        Schema::dropIfExists('pengeluarans');
        
        // (Omitted rollback for deleted jadwals and mata_pelajarans because it would require rebuilding heavy table structures)
    }
};
