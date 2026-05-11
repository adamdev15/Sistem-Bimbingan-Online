<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\KehadiranSiswa;
use App\Models\MateriLes;
use App\Models\Siswa;
use App\Models\Tutor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use App\Models\Payment;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PresensiController extends Controller
{
    private function actorCabangId(Request $request): ?int
    {
        $user = $request->user();
        if ($user->hasRole('admin_cabang')) {
            return Cabang::query()->where('user_id', $user->id)->value('id') ?: 0;
        }
        return null;
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $isSiswa = $user->hasRole('siswa');
        
        $query = KehadiranSiswa::query()
            ->with(['siswa', 'materiLes', 'creator', 'cabang'])
            ->latest('tanggal')
            ->latest('jam_mulai');

        if ($isSiswa) {
            $siswaId = Siswa::where('user_id', $user->id)->value('id');
            $query->where('student_id', $siswaId);
        } else {
            $cabangId = $this->actorCabangId($request);
            if ($user->hasRole('super_admin') && $request->filled('cabang_id')) {
                $cabangId = $request->integer('cabang_id');
            }

            if ($cabangId) {
                $query->where('cabang_id', $cabangId);
            }

            if ($request->filled('tanggal')) {
                $query->whereDate('tanggal', $request->date('tanggal'));
            }

            if ($request->filled('month')) {
                $m = \Carbon\Carbon::parse($request->month);
                $query->whereMonth('tanggal', $m->month)
                      ->whereYear('tanggal', $m->year);
            } elseif ($request->filled('bulan') && $request->filled('tahun')) {
                $query->whereMonth('tanggal', $request->integer('bulan'))
                      ->whereYear('tanggal', $request->integer('tahun'));
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('materi_les_id')) {
                $query->where('materi_les_id', $request->materi_les_id);
            }
        }

        $presensis = $query->paginate(15)->withQueryString();

        $materis = MateriLes::all();
        $cabangs = collect();
        if (!$isSiswa) {
            if ($user->hasRole('super_admin')) {
                $cabangs = Cabang::all();
            }
        }

        return view('modules.presensi.index', [
            'presensis' => $presensis,
            'isSiswa' => $isSiswa,
            'materis' => $materis,
            'cabangs' => $cabangs,
            'filters' => $request->only(['tanggal', 'status', 'materi_les_id', 'bulan', 'tahun', 'cabang_id', 'month'])
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        abort_unless($user->hasAnyRole(['super_admin', 'admin_cabang']), 403);
        
        $cabangId = $this->actorCabangId($request);

        $tutors = Tutor::where('status', 'aktif')->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))->get();
        $materis = MateriLes::all();
        $siswas = Siswa::where('status', 'aktif')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->orderBy('nama')
            ->get();

        return view('modules.presensi.create', [
            'materis' => $materis,
            'siswas' => $siswas,
            'cabangId' => $cabangId,
        ]);
    }

    public function storeSesi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'materi_les_id' => 'required|exists:materi_les,id',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'statuses' => 'required|array',
            'statuses.*' => 'required|in:hadir,izin,alfa,sakit',
        ]);

        $user = $request->user();
        $cabangId = $this->actorCabangId($request);
        $cabangToUse = $cabangId;

        DB::transaction(function () use ($validated, $user, $cabangToUse, $request) {
            foreach ($validated['statuses'] as $studentId => $status) {
                KehadiranSiswa::updateOrCreate(
                    [
                        'student_id' => (int) $studentId,
                        'materi_les_id' => $validated['materi_les_id'],
                        'tanggal' => $validated['tanggal'],
                        'jam_mulai' => $validated['jam_mulai'],
                        'jam_selesai' => $validated['jam_selesai'],
                    ],
                    [
                        'cabang_id' => $cabangToUse,
                        'status' => $status,
                        'created_by' => $user->id,
                        'catatan' => $request->input('catatans.'.$studentId),
                    ]
                );
            }
        });

        return redirect()->route('presensi.index')->with('status', 'Absensi kelas berhasil dicatat.');
    }

    public function export(Request $request): StreamedResponse
    {
        $cabangId = $this->actorCabangId($request);
        
        $rows = KehadiranSiswa::query()
            ->with(['siswa', 'materiLes', 'creator', 'cabang'])
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->when($request->filled('tanggal'), fn($q) => $q->whereDate('tanggal', $request->date('tanggal')))
            ->when($request->filled('materi_les_id'), fn($q) => $q->where('materi_les_id', $request->materi_les_id))
            ->latest('tanggal')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Jam', 'Nama Siswa', 'Materi', 'Status', 'Dicatat oleh']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    optional($row->tanggal)->format('Y-m-d'),
                    substr($row->jam_mulai, 0, 5) . '-' . substr($row->jam_selesai, 0, 5),
                    optional($row->siswa)->nama,
                    optional($row->materiLes)->nama_materi,
                    $row->status,
                    optional($row->creator)->name ?? '',
                ]);
            }
            fclose($handle);
        }, 'rekap-presensi.csv', ['Content-Type' => 'text/csv']);
    }
    public function getTutorsByCabang(Cabang $cabang)
    {
        return response()->json(
            Tutor::where('cabang_id', $cabang->id)
                ->where('status', 'aktif')
                ->select('id', 'nama')
                ->get()
        );
    }

    public function getStudentsByCabang(Cabang $cabang)
    {
        return response()->json(
            Siswa::where('cabang_id', $cabang->id)
                ->where('status', 'aktif')
                ->select('id', 'nama', 'created_at')
                ->get()
        );
    }

    public function printCard(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:siswas,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $siswa = Siswa::with(['cabang', 'materiLes'])->findOrFail($request->student_id);
        
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        
        // We will show 6 periods ending at the selected end_date period.
        // Actually, the user wants "Periode Dari" and "Periode Sampai" for the current period,
        // and then 5 periods before that.
        
        $monthsData = [];
        $regDay = $siswa->created_at?->day ?? 1;

        // Current Selected Period
        $periods = [];
        $currStart = $startDate->copy();
        $currEnd = $endDate->copy();

        for ($i = 0; $i <= 5; $i++) {
            // Calculate start and end for this period
            // If i=0, use selected dates. If i > 0, we go back by months based on regDay.
            if ($i === 0) {
                $pStart = $currStart->copy();
                $pEnd = $currEnd->copy();
            } else {
                // Go back i months from the START of the selected period, adjusting to regDay
                $pStart = $currStart->copy()->subMonths($i)->day($regDay);
                $pEnd = $pStart->copy()->addMonth()->subDay();
            }

            // Fetch attendance for this period
            $kehadirans = KehadiranSiswa::where('student_id', $siswa->id)
                ->whereBetween('tanggal', [$pStart->format('Y-m-d'), $pEnd->format('Y-m-d')])
                ->where('status', 'hadir')
                ->orderBy('tanggal')
                ->get();
            
            // For the new requirement: list actual dates (d/m) in cells
            $presenceDates = $kehadirans->map(fn($k) => $k->tanggal->format('j/n'))->toArray();

            // Get SPP payment for this period
            // Check by invoice_period (Y-m) or if payment date falls within period
            $payment = Payment::where('student_id', $siswa->id)
                ->where(function($q) use ($pStart, $pEnd) {
                    $q->whereBetween('tanggal_bayar', [$pStart->format('Y-m-d'), $pEnd->format('Y-m-d')])
                      ->orWhere('invoice_period', $pStart->format('Y-m'));
                })
                ->whereHas('fee', function($q) {
                    $q->where('tipe', 'bulanan');
                })
                ->orderByDesc('id')
                ->first();

            $monthsData[] = [
                'periodLabel' => $pStart->format('j M') . ' - ' . $pEnd->format('j M'),
                'fullPeriodLabel' => $pStart->format('j') . ' ' . $pStart->translatedFormat('F') . ' - ' . $pEnd->format('j') . ' ' . $pEnd->translatedFormat('F'),
                'presenceDates' => $presenceDates,
                'payment' => $payment,
            ];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.kartu-absensi', [
            'siswa' => $siswa,
            'monthsData' => $monthsData,
        ]);

        return $pdf->stream("Kartu-Absensi-{$siswa->nama}.pdf");
    }

    public function update(Request $request, KehadiranSiswa $presensi): RedirectResponse
    {
        $user = $request->user();
        $cabangId = $this->actorCabangId($request);

        if (!$user->hasRole('super_admin')) {
            abort_unless((int) $cabangId === (int) $presensi->cabang_id, 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:hadir,izin,alfa,sakit',
            'catatan' => 'nullable|string',
        ]);

        $presensi->update($validated);

        return back()->with('status', 'Data absensi berhasil diperbarui.');
    }

    public function destroy(Request $request, KehadiranSiswa $presensi): RedirectResponse
    {
        $user = $request->user();
        $cabangId = $this->actorCabangId($request);

        if (!$user->hasRole('super_admin')) {
            abort_unless((int) $cabangId === (int) $presensi->cabang_id, 403);
        }

        $presensi->delete();

        return back()->with('status', 'Data absensi berhasil dihapus.');
    }
}
