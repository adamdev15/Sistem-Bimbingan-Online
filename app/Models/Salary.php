<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salary extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tutor_id',
        'periode',
        'status',
        'total_kehadiran',
        'full',
        'pagi_siang',
        'siang_sore',
        'gaji',
        'insentif_kehadiran',
        'bonus_lainnya',
        'total_gaji',
        'created_by',
        'status',
        'catatan',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'total_kehadiran' => 'float',
            'gaji' => 'decimal:2',
            'insentif_kehadiran' => 'decimal:2',
            'bonus_lainnya' => 'decimal:2',
            'total_gaji' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
