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
        'total_jam',
        'total_gaji',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total_gaji' => 'decimal:2',
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
