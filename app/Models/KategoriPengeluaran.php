<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPengeluaran extends Model
{
    protected $fillable = ['nama_kategori'];

    public function pengeluarans(): HasMany
    {
        return $this->hasMany(Pengeluaran::class, 'kategori_id');
    }
}
