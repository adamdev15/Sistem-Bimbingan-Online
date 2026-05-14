<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_id',
        'nama_item',
        'qty',
        'tarif',
        'subtotal',
        'keterangan',
    ];

    protected $casts = [
        'qty' => 'float',
        'tarif' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function salary(): BelongsTo
    {
        return $this->belongsTo(Salary::class);
    }
}
