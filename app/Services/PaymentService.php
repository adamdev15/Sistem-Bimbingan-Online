<?php

namespace App\Services;

use App\Models\Payment;
use App\Repositories\PaymentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentService
{
    public function __construct(private readonly PaymentRepository $repository)
    {
    }

    public function list(int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function create(array $payload): Payment
    {
        return $this->repository->create($payload);
    }
}
