<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchMateriPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'cabang_id',
        'materi_les_id',
        'biaya_daftar',
        'biaya_spp',
    ];

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function materiLes(): BelongsTo
    {
        return $this->belongsTo(MateriLes::class, 'materi_les_id');
    }
}
