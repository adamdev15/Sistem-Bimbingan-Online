<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tutor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'foto',
        'nama',
        'email',
        'nik',
        'no_hp',
        'alamat',
        'cabang_id',
        'status',
        'jenis_tutor',
        'jabatan',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function cabangs(): BelongsToMany
    {
        return $this->belongsToMany(Cabang::class, 'cabang_tutor');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kehadiranTutors(): HasMany
    {
        return $this->hasMany(KehadiranTutor::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }
}
