<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jadwal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tutor_id',
        'cabang_id',
        'mapel',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function kehadirans(): HasMany
    {
        return $this->hasMany(Kehadiran::class);
    }
}
