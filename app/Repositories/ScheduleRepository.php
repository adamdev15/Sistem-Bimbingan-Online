<?php

namespace App\Repositories;

use App\Models\Jadwal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ScheduleRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Jadwal::query()->with(['tutor', 'cabang'])->latest()->paginate($perPage);
    }

    public function create(array $payload): Jadwal
    {
        return Jadwal::query()->create($payload);
    }
}
