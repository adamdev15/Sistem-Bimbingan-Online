<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiswaController extends Controller
{
    public function __construct(private readonly ManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('modules.tutor.siswa-index', [
            'siswas' => $this->service->tutorSiswaIndex($request),
            'filters' => $request->only(['search', 'jenis_kelamin', 'status']),
        ]);
    }
}
