<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Payment::query()->with(['siswa', 'fee'])->latest()->paginate($perPage);
    }

    public function create(array $payload): Payment
    {
        return Payment::query()->create($payload);
    }
}
