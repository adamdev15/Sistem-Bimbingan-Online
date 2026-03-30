<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cabang extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'foto',
        'nama_cabang',
        'alamat',
        'kota',
        'telepon',
        'status',
    ];

    public function siswas(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    public function tutors(): HasMany
    {
        return $this->hasMany(Tutor::class);
    }

    public function jadwals(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }
}
