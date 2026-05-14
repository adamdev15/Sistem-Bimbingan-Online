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
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'lain_lainnya' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama_item' => ['required', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0'],
            'items.*.tarif' => ['required', 'numeric', 'min:0'],
            'items.*.subtotal' => ['required', 'numeric', 'min:0'],
            'items.*.keterangan' => ['nullable', 'string'],
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

        $itemsTotal = 0;
        $salaryData = [
            'tutor_id' => $data['tutor_id'],
            'periode' => $data['periode'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'bonus' => (float) ($data['bonus'] ?? 0),
            'lain_lainnya' => (float) ($data['lain_lainnya'] ?? 0),
            'status' => $data['status'] ?? 'pending',
            'catatan' => $data['catatan'] ?? null,
            'created_by' => $request->user()?->id,
        ];

        // We'll calculate the total_gaji properly by summing items
        foreach ($data['items'] as $item) {
            $itemsTotal += (float) $item['qty'] * (float) $item['tarif'];
        }
        
        $salaryData['total_gaji'] = $itemsTotal + $salaryData['bonus'] + $salaryData['lain_lainnya'];

        $salary = Salary::query()->create($salaryData);

        foreach ($data['items'] as $item) {
            $salary->items()->create([
                'nama_item' => $item['nama_item'],
                'qty' => $item['qty'],
                'tarif' => $item['tarif'],
                'subtotal' => (float) $item['qty'] * (float) $item['tarif'],
                'keterangan' => $item['keterangan'] ?? null,
            ]);
        }

        if (in_array($salary->status, ['dibayar', 'diterima'])) {
            $this->whatsapp->notifyTutorSalaryPaid($salary);
        }

        return back()->with('status', 'Data gaji tutor berhasildisimpan.');
    }

    public function getAttendanceCount(Request $request): JsonResponse
    {
        $tutorId = $request->integer('tutor_id');
        $startDate = $request->string('start_date')->toString();
        $endDate = $request->string('end_date')->toString();

        if (!$tutorId) {
            return response()->json(['items' => []]);
        }

        $query = KehadiranTutor::query()
            ->where('tutor_id', $tutorId)
            ->where('status', 'hadir');

        if ($startDate && $endDate) {
            $query->whereBetween('tanggal', [$startDate, $endDate]);
        } else {
             return response()->json(['items' => []]);
        }

        $counts = $query->selectRaw('kehadiran, count(*) as qty')
            ->groupBy('kehadiran')
            ->pluck('qty', 'kehadiran')
            ->toArray();

        $types = [
            'full' => 'Full',
            'pagi_siang' => 'Pagi-Siang',
            'siang_sore' => 'Siang-Sore',
            'kelas_malam' => 'Kelas Malam',
        ];

        $items = [];
        foreach ($types as $key => $label) {
            $items[] = [
                'nama_item' => $label,
                'qty' => (float) ($counts[$key] ?? 0),
                'tarif' => 0,
                'subtotal' => 0,
            ];
        }

        return response()->json([
            'items' => $items,
        ]);
    }

    public function update(Request $request, Salary $salary): RedirectResponse
    {
        $this->guardSalaryCabang($salary);

        $data = $request->validate([
            'tutor_id' => ['required', 'exists:tutors,id'],
            'periode' => ['required', 'string', 'max:64'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'bonus' => ['nullable', 'numeric', 'min:0'],
            'lain_lainnya' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.nama_item' => ['required', 'string'],
            'items.*.qty' => ['required', 'numeric', 'min:0'],
            'items.*.tarif' => ['required', 'numeric', 'min:0'],
            'items.*.keterangan' => ['nullable', 'string'],
            'status' => ['required', 'in:pending,dibayar,diterima'],
            'catatan' => ['nullable', 'string'],
        ]);

        $itemsTotal = 0;
        foreach ($data['items'] as $item) {
            $itemsTotal += (float) $item['qty'] * (float) $item['tarif'];
        }

        $salaryData = [
            'tutor_id' => $data['tutor_id'],
            'periode' => $data['periode'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'bonus' => (float) ($data['bonus'] ?? 0),
            'lain_lainnya' => (float) ($data['lain_lainnya'] ?? 0),
            'total_gaji' => $itemsTotal + (float) ($data['bonus'] ?? 0) + (float) ($data['lain_lainnya'] ?? 0),
            'status' => $data['status'],
            'catatan' => $data['catatan'] ?? null,
        ];

        $beforeStatus = $salary->status;
        $salary->update($salaryData);

        // Replace items
        $salary->items()->delete();
        foreach ($data['items'] as $item) {
            $salary->items()->create([
                'nama_item' => $item['nama_item'],
                'qty' => $item['qty'],
                'tarif' => $item['tarif'],
                'subtotal' => (float) $item['qty'] * (float) $item['tarif'],
                'keterangan' => $item['keterangan'] ?? null,
            ]);
        }

        if ($beforeStatus !== $salary->status && in_array($salary->status, ['dibayar', 'diterima'])) {
            $this->whatsapp->notifyTutorSalaryPaid($salary);
        }

        return back()->with('status', 'Data gaji tutor berhasil diperbarui.');
    }

    public function destroy(Salary $salary): RedirectResponse
    {
        $this->guardSalaryCabang($salary);
        $salary->delete();
        return back()->with('status', 'Data gaji tutor berhasil dihapus.');
    }

    public function printSlip(Salary $salary): Response
    {
        $this->guardSalaryCabang($salary);
        $salary->load(['tutor.cabang', 'items', 'creator']);
        
        $name = 'slip-gaji-' . str_replace(' ', '-', strtolower($salary->tutor->nama)) . '-' . $salary->periode . '.pdf';

        return Pdf::loadView('exports.slip-gaji-pdf', compact('salary'))
            ->setPaper('a5', 'landscape')
            ->stream($name);
    }

    private function tutorsForSalaryForm(): Collection
    {
        $cabangId = Cabang::query()->where('user_id', auth()->id())->value('id');

        return Tutor::query()
            ->with('cabang:id,nama_cabang')
            ->select('id', 'nama', 'cabang_id', 'jenis_tutor')
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
