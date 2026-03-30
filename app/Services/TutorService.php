<?php

namespace App\Services;

use App\Models\Tutor;
use App\Repositories\TutorRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TutorService
{
    public function __construct(private readonly TutorRepository $repository)
    {
    }

    public function list(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function create(array $payload): Tutor
    {
        return $this->repository->create($payload);
    }

    public function update(Tutor $tutor, array $payload): Tutor
    {
        return $this->repository->update($tutor, $payload);
    }
}
