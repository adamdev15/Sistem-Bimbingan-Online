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
        'bonus',
        'lain_lainnya',
        'total_gaji',
        'created_by',
        'catatan',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'bonus' => 'decimal:2',
            'lain_lainnya' => 'decimal:2',
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

    public function items()
    {
        return $this->hasMany(SalaryItem::class);
    }
}
