<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'foto',
        'nama',
        'email',
        'jenis_kelamin',
        'nik',
        'no_hp',
        'alamat',
        'cabang_id',
        'status',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    public function kehadirans(): HasMany
    {
        return $this->hasMany(Kehadiran::class, 'student_id');
    }
}
