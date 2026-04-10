<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Exports\GajiTutorLaporanExport;
use App\Http\Controllers\Controller;
use App\Models\Kehadiran;
use App\Models\Cabang;
use App\Models\Salary;
use App\Models\Tutor;
use App\Services\SuperAdmin\ManagementService;
use App\Services\WhatsApp\WhatsAppNotifier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SalaryController extends Controller
{
    public function __construct(
        private readonly ManagementService $service,
        private readonly WhatsAppNotifier $whatsapp,
    ) {}

    public function index(Request $request): View
    {
        return view('modules.salaries.index', [
            'salaries' => $this->service->salaryIndex($request),
            'tutors' => $this->tutorsForSalaryForm(),
            'filters' => $request->only(['status', 'tutor_id']),
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $this->assertSalaryExportAllowed($request);
        $payload = $this->service->salaryReportPayload($request);
        $name = 'gaji-tutor-laporan-'.now()->format('Y-m-d-His').'.pdf';

        return Pdf::loadView('exports.gaji-tutor-laporan-pdf', $payload)
            ->setPaper('a4', 'landscape')
            ->stream($name);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $this->assertSalaryExportAllowed($request);
        $payload = $this->service->salaryReportPayload($request);
        $name = 'gaji-tutor-laporan-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(new GajiTutorLaporanExport($payload), $name);
    }

    private function assertSalaryExportAllowed(Request $request): void
    {
        $user = $request->user();
        abort_unless($user && $user->hasAnyRole(['super_admin', 'admin_cabang']), 403);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tutor_id' => ['required', 'exists:tutors,id'],
            'periode' => ['required', 'string', 'max:64'],
            'total_kehadiran' => ['required', 'integer', 'min:0'],
            'total_gaji' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pending,dibayar,diterima'],
            'catatan' => ['nullable', 'string'],
        ]);

        $this->guardTutorCabang((int) $data['tutor_id']);

        Salary::query()->create([
            'tutor_id' => $data['tutor_id'],
            'periode' => $data['periode'],
            'total_kehadiran' => $data['total_kehadiran'],
            'total_gaji' => $data['total_gaji'],
            'status' => $data['status'] ?? 'pending',
            'catatan' => $data['catatan'],
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Data gaji disimpan.');
    }

    public function getAttendanceCount(Request $request): JsonResponse
    {
        $tutorId = $request->integer('tutor_id');
        $periode = $request->string('periode')->toString(); // Expecting YYYY-MM

        if (!$tutorId || !preg_match('/^\d{4}-\d{2}$/', $periode)) {
            return response()->json(['count' => 0]);
        }

        [$year, $month] = explode('-', $periode);

        $count = Kehadiran::query()
            ->where('tutor_id', $tutorId)
            ->whereYear('tanggal', (int) $year)
            ->whereMonth('tanggal', (int) $month)
            ->where('status', 'hadir')
            ->count();

        return response()->json(['count' => $count]);
    }

    public function update(Request $request, Salary $salary): RedirectResponse
    {
        $this->guardSalaryCabang($salary);

        $data = $request->validate([
            'status' => ['required', 'in:pending,dibayar,diterima'],
        ]);

        $before = $salary->status;
        $salary->update($data);
        $salary->refresh();

        if ($before !== 'dibayar' && $salary->status === 'dibayar') {
            $this->whatsapp->notifyTutorSalaryPaid($salary);
        }

        return back()->with('status', 'Status gaji diperbarui.');
    }

    private function tutorsForSalaryForm(): Collection
    {
        $cabangId = Cabang::query()->where('user_id', auth()->id())->value('id');

        return Tutor::query()
            ->select('id', 'nama', 'cabang_id')
            ->when(
                auth()->user()?->hasRole('admin_cabang') && $cabangId,
                fn ($q) => $q->where('cabang_id', $cabangId)
            )
            ->orderBy('nama')
            ->get();
    }

    private function guardTutorCabang(int $tutorId): void
    {
        $user = auth()->user();
        if (! $user || $user->hasRole('super_admin')) {
            return;
        }

        if (! $user->hasRole('admin_cabang')) {
            abort(403);
        }

        $adminCabangId = Cabang::query()->where('user_id', $user->id)->value('id');
        $tutorCabang = Tutor::query()->whereKey($tutorId)->value('cabang_id');
        if ((int) $adminCabangId !== (int) $tutorCabang) {
            abort(403);
        }
    }

    private function guardSalaryCabang(Salary $salary): void
    {
        $user = auth()->user();
        if (! $user || $user->hasRole('super_admin')) {
            return;
        }

        if (! $user->hasRole('admin_cabang')) {
            abort(403);
        }

        $adminCabangId = Cabang::query()->where('user_id', $user->id)->value('id');
        $tutorCabang = $salary->tutor?->cabang_id;
        if ((int) $adminCabangId !== (int) $tutorCabang) {
            abort(403);
        }
    }
}
