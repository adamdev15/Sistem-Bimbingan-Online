<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PembayaranController extends Controller
{
    public function __construct(private readonly ManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('modules.pembayaran.index', [
            'payments' => $this->service->pembayaranIndex($request),
            'summary' => $this->service->pembayaranSummary($request),
            'fees' => $this->service->feesForSelect(),
            'students' => $this->service->studentsForSelect(),
            'filters' => $request->only(['status', 'student_id', 'bulan']),
        ]);
    }

    public function massStore(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['required', 'exists:siswas,id'],
            'biaya_id' => ['required', 'exists:fees,id'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'tanggal_bayar' => ['required', 'date'],
        ]);

        foreach ($data['student_ids'] as $studentId) {
            Payment::create([
                'student_id' => $studentId,
                'biaya_id' => $data['biaya_id'],
                'nominal' => $data['nominal'],
                'tanggal_bayar' => $data['tanggal_bayar'],
                'status' => 'belum',
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Tagihan massal berhasil dibuat.',
                'count' => count($data['student_ids']),
            ]);
        }

        return back()->with('status', 'Tagihan massal berhasil dibuat.');
    }
}
