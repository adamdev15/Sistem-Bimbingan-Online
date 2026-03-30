<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_biaya',
        'nominal',
        'tipe',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'biaya_id');
    }
}
