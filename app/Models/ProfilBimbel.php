<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfilBimbel extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_bimbel',
        'alamat',
        'no_hp',
        'logo',
        'banner',
    ];
}
