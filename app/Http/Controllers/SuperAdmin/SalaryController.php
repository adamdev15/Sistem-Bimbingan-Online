<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Exports\GajiTutorLaporanExport;
use App\Http\Controllers\Controller;
use App\Models\KehadiranTutor;
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

use App\Exports\GajiTutorExport;

class SalaryController extends Controller
{
    public function exportExcel(Request $request): BinaryFileResponse
    {
        $query = $this->service->salaryFilteredQuery($request);

        $name = 'gaji-tutor-export-' . now()->format('Y-m-d-His') . '.xlsx';
        
        return Excel::download(new GajiTutorExport($query), $name);
    }

    public function __construct(
        private readonly ManagementService $service,
        private readonly WhatsAppNotifier $whatsapp,
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $cabangId = $user->hasRole('admin_cabang') 
            ? \App\Models\Cabang::where('user_id', $user->id)->value('id') 
            : null;

        $stats = [
            'pending' => Salary::where('status', 'pending')
                ->when($cabangId, fn($q) => $q->whereHas('tutor', fn($t) => $t->where('cabang_id', $cabangId)))
                ->count(),
            'dibayar' => Salary::where('status', 'dibayar')
                ->when($cabangId, fn($q) => $q->whereHas('tutor', fn($t) => $t->where('cabang_id', $cabangId)))
                ->count(),
            'total_rp' => Salary::whereIn('status', ['dibayar', 'diterima'])
                ->when($cabangId, fn($q) => $q->whereHas('tutor', fn($t) => $t->where('cabang_id', $cabangId)))
                ->sum('total_gaji'),
        ];

        return view('modules.salaries.index', [
            'salaries' => $this->service->salaryIndex($request),
            'tutors' => $this->tutorsForSalaryForm(),
            'filters' => $request->only(['status', 'tutor_id']),
            'stats' => $stats,
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
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'total_kehadiran' => ['required', 'numeric', 'min:0'],
            'full' => ['required', 'integer', 'min:0'],
            'pagi_siang' => ['required', 'integer', 'min:0'],
            'siang_sore' => ['required', 'integer', 'min:0'],
            'gaji' => ['required', 'numeric', 'min:0'],
            'insentif_kehadiran' => ['required', 'numeric', 'min:0'],
            'bonus_lainnya' => ['required', 'numeric', 'min:0'],
            'total_gaji' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pending,dibayar,diterima'],
            'catatan' => ['nullable', 'string'],
        ]);

        $this->guardTutorCabang((int) $data['tutor_id']);

        // Check if salary for this tutor and period already exists
        $exists = Salary::where('tutor_id', $data['tutor_id'])
            ->where('periode', $data['periode'])
            ->first();

        if ($exists) {
            $tutor = Tutor::find($data['tutor_id']);
            return back()
                ->withInput()
                ->with('error', "Gaji tutor {$tutor->nama} periode {$data['periode']} telah diinputkan, cek data terlebih dahulu.");
        }

        $salary = Salary::query()->create([
            'tutor_id' => $data['tutor_id'],
            'periode' => $data['periode'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'total_kehadiran' => $data['total_kehadiran'],
            'full' => $data['full'],
            'pagi_siang' => $data['pagi_siang'],
            'siang_sore' => $data['siang_sore'],
            'gaji' => $data['gaji'],
            'insentif_kehadiran' => $data['insentif_kehadiran'],
            'bonus_lainnya' => $data['bonus_lainnya'],
            'total_gaji' => $data['total_gaji'],
            'status' => $data['status'] ?? 'pending',
            'catatan' => $data['catatan'],
            'created_by' => $request->user()?->id,
        ]);

        if (in_array($salary->status, ['dibayar', 'diterima'])) {
            $this->whatsapp->notifyTutorSalaryPaid($salary);
        }

        return back()->with('status', 'Data gaji disimpan.');
    }

    public function getAttendanceCount(Request $request): JsonResponse
    {
        $tutorId = $request->integer('tutor_id');
        $periode = $request->string('periode')->toString();
        $startDate = $request->string('start_date')->toString();
        $endDate = $request->string('end_date')->toString();

        if (!$tutorId) {
            return response()->json(['count' => 0, 'full' => 0, 'pagi_siang' => 0, 'siang_sore' => 0]);
        }

        $query = KehadiranTutor::query()
            ->where('tutor_id', $tutorId)
            ->where('status', 'hadir');

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        } elseif ($periode && preg_match('/^\d{4}-\d{2}$/', $periode)) {
            [$year, $month] = explode('-', $periode);
            $query->whereYear('tanggal', (int) $year)
                  ->whereMonth('tanggal', (int) $month);
        } else {
            return response()->json(['count' => 0, 'full' => 0, 'pagi_siang' => 0, 'siang_sore' => 0]);
        }

        $kehadirans = $query->get();
        $full = $kehadirans->where('kehadiran', 'full')->count();
        $pagi_siang = $kehadirans->where('kehadiran', 'pagi_siang')->count();
        $siang_sore = $kehadirans->where('kehadiran', 'siang_sore')->count();
        $count = $kehadirans->count();

        return response()->json([
            'count' => $count,
            'full' => $full,
            'pagi_siang' => $pagi_siang,
            'siang_sore' => $siang_sore,
        ]);
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

        if ($before !== $salary->status && in_array($salary->status, ['dibayar', 'diterima'])) {
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
