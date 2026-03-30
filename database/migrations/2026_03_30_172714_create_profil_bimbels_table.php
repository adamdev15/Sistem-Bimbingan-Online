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
        Schema::create('profil_bimbels', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bimbel');
            $table->text('alamat');
            $table->string('no_hp', 25)->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profil_bimbels');
    }
};
