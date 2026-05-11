<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'foto',
        'nama',
        'email',
        'jenis_kelamin',
        'nik',
        'no_hp',
        'alamat',
        'cabang_id',
        'status',
        'tempat_lahir',
        'tanggal_lahir',
        'asal_sekolah',
        'nis',
        'materi_les_id',
        'nama_ayah',
        'tempat_lahir_ayah',
        'tanggal_lahir_ayah',
        'pekerjaan_ayah',
        'nama_ibu',
        'tempat_lahir_ibu',
        'tanggal_lahir_ibu',
        'pekerjaan_ibu',
        'no_hp_orang_tua',
        'cuti_sampai',
        'registration_type',
    ];

    public function materiLes(): BelongsTo
    {
        return $this->belongsTo(MateriLes::class, 'materi_les_id');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'student_id');
    }

    public function kehadiranSiswas(): HasMany
    {
        return $this->hasMany(KehadiranSiswa::class, 'student_id');
    }

    public function phoneForNotification(): ?string
    {
        return $this->no_hp ?: $this->no_hp_orang_tua;
    }
}
