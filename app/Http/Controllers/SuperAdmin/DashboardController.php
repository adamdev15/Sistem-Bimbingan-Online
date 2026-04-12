<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ManagementService $service)
    {
    }

    public function __invoke(): View
    {
        return view('dashboard', [
            'dashboardData' => $this->service->dashboardStats(),
        ]);
    }

    public function keuanganChart(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse
    {
        $filter = $request->query('filter', 'monthly');
        $data = $this->service->getKeuanganChartData($filter);
        return response()->json($data);
    }
}
