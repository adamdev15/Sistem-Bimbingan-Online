<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Kehadiran;
use App\Models\Tutor;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PresensiController extends Controller
{
    public function __construct(private readonly ManagementService $service) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('modules.presensi.index', [
            'presensis' => $this->service->presensiIndex($request),
            'summary' => $this->service->presensiSummary($request),
            'presensi_jadwals' => $user?->hasRole('siswa')
                ? $this->service->presensiJadwalFilterOptionsForSiswa()
                : collect(),
            'presensi_jadwal_rekap' => $user?->hasAnyRole(['super_admin', 'admin_cabang'])
                ? $this->service->presensiJadwalFilterOptionsForRekap()
                : collect(),
            'kelas_context' => $user?->hasRole('tutor')
                ? $this->service->presensiTutorKelasContext($request)
                : null,
            'tutor_jadwal_choices' => $user?->hasRole('tutor')
                ? $this->service->presensiJadwalFilterOptionsForRekap()
                : collect(),
            'filters' => $request->only(['tanggal', 'status', 'jadwal_id', 'kelas_jadwal_id', 'kelas_tanggal']),
        ]);
    }

    public function storeSesi(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user && $user->hasRole('tutor'), 403);

        $validated = $request->validate([
            'jadwal_id' => ['required', 'exists:jadwals,id'],
            'tanggal' => ['required', 'date'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['required', 'in:hadir,izin,alfa,sakit'],
        ]);

        $jadwal = Jadwal::query()->findOrFail($validated['jadwal_id']);
        $tutorId = Tutor::query()->where('user_id', $user->id)->value('id');
        abort_if(! $tutorId || (int) $jadwal->tutor_id !== (int) $tutorId, 403);

        $enrolledIds = $jadwal->siswas()->pluck('siswas.id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $submittedIds = collect(array_keys($validated['statuses']))->map(fn ($id) => (int) $id)->sort()->values()->all();

        if ($enrolledIds === []) {
            return back()
                ->withErrors(['statuses' => 'Belum ada siswa terdaftar di kelas ini. Hubungi admin untuk menambah peserta di menu Jadwal.'])
                ->withInput()
                ->with('open_presensi_modal', true);
        }

        if ($enrolledIds !== $submittedIds) {
            return back()
                ->withErrors(['statuses' => 'Kirim presensi untuk seluruh peserta terdaftar.'])
                ->withInput()
                ->with('open_presensi_modal', true);
        }

        $sudahAdaData = Kehadiran::query()
            ->where('jadwal_id', $jadwal->id)
            ->whereDate('tanggal', $validated['tanggal'])
            ->exists();

        DB::transaction(function () use ($validated, $jadwal, $user): void {
            foreach ($validated['statuses'] as $studentId => $status) {
                Kehadiran::query()->updateOrCreate(
                    [
                        'student_id' => (int) $studentId,
                        'jadwal_id' => $jadwal->id,
                        'tanggal' => $validated['tanggal'],
                    ],
                    [
                        'status' => $status,
                        'created_by' => $user->id,
                    ]
                );
            }
        });

        return redirect()->route('presensi.index', [
            'kelas_jadwal_id' => $jadwal->id,
            'kelas_tanggal' => $validated['tanggal'],
        ])->with('status', $sudahAdaData
            ? 'Kehadiran peserta diperbarui.'
            : 'Presensi kelas berhasil dicatat.');
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->service->presensiIndex($request)->getCollection();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Nama', 'Sesi', 'Tutor', 'Status', 'Dicatat oleh']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    optional($row->tanggal)->format('Y-m-d'),
                    optional($row->siswa)->nama,
                    optional($row->jadwal)->mapel,
                    optional($row->tutor)->nama ?? optional(optional($row->jadwal)->tutor)->nama ?? '',
                    $row->status,
                    optional($row->creator)->name ?? '',
                ]);
            }
            fclose($handle);
        }, 'rekap-presensi.csv', ['Content-Type' => 'text/csv']);
    }
}
