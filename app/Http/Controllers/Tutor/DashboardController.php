<?php

namespace App\Http\Controllers\Tutor;

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
            'dashboardData' => $this->service->tutorDashboardData(request()),
        ]);
    }
}
