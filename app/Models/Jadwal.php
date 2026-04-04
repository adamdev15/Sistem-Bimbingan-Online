<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jadwal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tutor_id',
        'cabang_id',
        'mata_pelajaran_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    protected function mapel(): Attribute
    {
        return Attribute::get(function (): string {
            if ($this->relationLoaded('mataPelajaran')) {
                return (string) ($this->mataPelajaran?->nama ?? '');
            }

            return (string) ($this->mataPelajaran()->value('nama') ?? '');
        });
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Tutor::class);
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function kehadirans(): HasMany
    {
        return $this->hasMany(Kehadiran::class);
    }

    /**
     * Siswa terdaftar di kelas ini (pivot student_class).
     *
     * @return BelongsToMany<Siswa, $this>
     */
    public function siswas(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'student_class', 'jadwal_id', 'student_id')
            ->withTimestamps();
    }
}
