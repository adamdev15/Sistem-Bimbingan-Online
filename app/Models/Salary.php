<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salary extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tutor_id',
        'periode',
        'total_jam',
        'total_gaji',
    ];

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }
}
