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
    ];

    public function branchPrices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BranchMateriPrice::class, 'materi_les_id');
    }
}
