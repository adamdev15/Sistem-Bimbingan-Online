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
        Schema::table('salaries', function (Blueprint $table) {
            // Drop columns no longer used in the new dynamic payroll system
            $table->dropColumn([
                'full', 
                'pagi_siang', 
                'siang_sore', 
                'gaji', 
                'insentif_kehadiran', 
                'bonus_lainnya', 
                'total_kehadiran'
            ]);
            
            // Add new flexible manual adjustment columns
            $table->decimal('bonus', 15, 2)->default(0)->after('end_date');
            $table->decimal('lain_lainnya', 15, 2)->default(0)->after('bonus');
        });

        Schema::create('salary_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_id')->constrained('salaries')->onDelete('cascade');
            $table->string('nama_item');
            $table->decimal('qty', 10, 2);
            $table->decimal('tarif', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_items');

        Schema::table('salaries', function (Blueprint $table) {
            $table->integer('full')->default(0);
            $table->integer('pagi_siang')->default(0);
            $table->integer('siang_sore')->default(0);
            $table->decimal('gaji', 15, 2)->default(0);
            $table->decimal('insentif_kehadiran', 15, 2)->default(0);
            $table->decimal('bonus_lainnya', 15, 2)->default(0);
            $table->decimal('total_kehadiran', 10, 2)->default(0);
            
            $table->dropColumn(['bonus', 'lain_lainnya']);
        });
    }
};
