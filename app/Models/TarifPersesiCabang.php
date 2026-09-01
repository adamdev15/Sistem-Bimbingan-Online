<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TarifPersesiCabang extends Model
{
    protected $fillable = [
        'cabang_id',
        'nominal',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }
}
