<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KehadiranSiswa extends Model
{
    use HasFactory;

    protected $table = 'kehadiran_siswas';

    protected $fillable = [
        'student_id',
        'tanggal',
        'status',
        'created_by',
        'materi_les_id',
        'cabang_id',
        'jam_mulai',
        'jam_selesai',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'student_id');
    }

    public function materiLes(): BelongsTo
    {
        return $this->belongsTo(MateriLes::class, 'materi_les_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }
}
