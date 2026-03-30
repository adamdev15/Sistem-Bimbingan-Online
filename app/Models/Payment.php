<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'biaya_id',
        'nominal',
        'tanggal_bayar',
        'status',
    ];

    protected $casts = [
        'tanggal_bayar' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'biaya_id');
    }
}
