<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('order_id')->nullable()->unique()->after('id');
            $table->string('invoice_period', 7)->nullable()->after('biaya_id')->comment('YYYY-MM untuk tagihan bulanan (SPP)');
            $table->date('due_date')->nullable()->after('tanggal_bayar');
            $table->timestamp('paid_at')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->string('midtrans_payment_type')->nullable();
            $table->string('midtrans_transaction_status')->nullable();
            $table->json('midtrans_payload')->nullable();
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn([
                'order_id',
                'invoice_period',
                'due_date',
                'paid_at',
                'midtrans_transaction_id',
                'midtrans_payment_type',
                'midtrans_transaction_status',
                'midtrans_payload',
                'created_by',
            ]);
        });
    }
};
