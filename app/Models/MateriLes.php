<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MateriLes extends Model
{
    protected $table = 'materi_les';

    protected $fillable = [
        'nama_materi',
        'deskripsi',
        'foto',
        'pertemuan_per_minggu',
        'fee_id',
        'biaya_daftar',
    ];

    public function fee(): BelongsTo
    {
        return $this->belongsTo(Fee::class, 'fee_id');
    }
}
