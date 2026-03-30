<?php

namespace App\Repositories;

use App\Models\Siswa;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StudentRepository
{
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Siswa::query()->with('cabang')->latest()->paginate($perPage);
    }

    public function create(array $payload): Siswa
    {
        return Siswa::query()->create($payload);
    }

    public function update(Siswa $siswa, array $payload): Siswa
    {
        $siswa->update($payload);

        return $siswa->refresh();
    }
}
