<?php

namespace App\Repositories;

use App\Models\Tutor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TutorRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Tutor::query()->with('cabang')->latest()->paginate($perPage);
    }

    public function create(array $payload): Tutor
    {
        return Tutor::query()->create($payload);
    }

    public function update(Tutor $tutor, array $payload): Tutor
    {
        $tutor->update($payload);

        return $tutor->refresh();
    }
}
