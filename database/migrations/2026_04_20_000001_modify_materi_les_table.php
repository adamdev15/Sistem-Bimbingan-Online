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
            if (Schema::hasColumn('materi_les', 'fee_id')) {
                $table->dropForeign(['fee_id']);
                $table->dropColumn('fee_id');
            }
            if (!Schema::hasColumn('materi_les', 'biaya_tutor')) {
                $table->decimal('biaya_tutor', 15, 2)->default(0)->after('biaya_daftar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materi_les', function (Blueprint $table) {
            $table->foreignId('fee_id')->nullable()->constrained('fees')->nullOnDelete();
            $table->dropColumn('biaya_tutor');
        });
    }
};
