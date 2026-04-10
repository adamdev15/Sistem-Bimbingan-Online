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
        Schema::create('siswas', function (Blueprint $table) {
            $table->id();
            $table->string('foto')->nullable();
            $table->string('nama');
            $table->string('email')->unique();
            $table->enum('jenis_kelamin', ['laki_laki', 'perempuan']);
            $table->string('nik', 30)->nullable()->unique();
            $table->string('no_hp', 25);
            $table->text('alamat');
            $table->foreignId('cabang_id')->constrained('cabangs');
            $table->enum('status', ['aktif', 'nonaktif','cuti'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('siswas');
    }
};
