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
        Schema::table('fees', function (Blueprint $table) {
            $table->dropColumn('nominal');
            $table->text('deskripsi')->nullable()->after('nama_biaya');
        });
    }

    public function down(): void
    {
        Schema::table('fees', function (Blueprint $table) {
            $table->dropColumn('deskripsi');
            $table->decimal('nominal', 14, 2)->after('nama_biaya');
        });
    }
};
