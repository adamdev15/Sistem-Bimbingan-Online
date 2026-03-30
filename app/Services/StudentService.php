<?php

namespace App\Services;

use App\Models\Siswa;
use App\Repositories\StudentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StudentService
{
    public function __construct(private readonly StudentRepository $repository)
    {
    }

    public function list(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function create(array $payload): Siswa
    {
        return $this->repository->create($payload);
    }

    public function update(Siswa $siswa, array $payload): Siswa
    {
        return $this->repository->update($siswa, $payload);
    }
}
