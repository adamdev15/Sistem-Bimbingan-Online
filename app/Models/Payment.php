<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'student_id',
        'biaya_id',
        'invoice_period',
        'nominal',
        'tanggal_bayar',
        'due_date',
        'paid_at',
        'status',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'midtrans_transaction_status',
        'midtrans_payload',
        'created_by',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'midtrans_payload' => 'array',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'biaya_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeBelum(Builder $q): Builder
    {
        return $q->where('status', 'belum');
    }

    public function scopeLunas(Builder $q): Builder
    {
        return $q->where('status', 'lunas');
    }

    public function isLunas(): bool
    {
        return $this->status === 'lunas';
    }

    public function grossAmountIdr(): int
    {
        return (int) max(0, round((float) $this->nominal));
    }
}
