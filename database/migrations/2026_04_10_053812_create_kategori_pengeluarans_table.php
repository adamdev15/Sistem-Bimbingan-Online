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
        Schema::create('kategori_pengeluarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori');
            $table->timestamps();
        });

        // Insert default categories
        DB::table('kategori_pengeluarans')->insert([
            ['nama_kategori' => 'Operasional'],
            ['nama_kategori' => 'Sewa Tempat'],
            ['nama_kategori' => 'Listrik & Air'],
            ['nama_kategori' => 'Alat Tulis Kantor'],
            ['nama_kategori' => 'Lain-lain'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori_pengeluarans');
    }
};
