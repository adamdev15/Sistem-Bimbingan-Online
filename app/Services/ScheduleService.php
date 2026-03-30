<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Repositories\ScheduleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ScheduleService
{
    public function __construct(private readonly ScheduleRepository $repository)
    {
    }

    public function list(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function create(array $payload): Jadwal
    {
        return $this->repository->create($payload);
    }
}
