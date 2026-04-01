<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->enum('status', ['pending', 'dibayar', 'diterima'])->default('pending')->after('periode');
            $table->foreignId('created_by')->nullable()->after('total_gaji')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('salaries', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['status', 'created_by']);
        });
    }
};
