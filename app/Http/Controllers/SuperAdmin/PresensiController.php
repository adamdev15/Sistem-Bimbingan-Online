<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Kehadiran;
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
        
        $query = Kehadiran::query()
            ->with(['siswa', 'tutor', 'materiLes', 'creator', 'cabang'])
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
        $siswas = Siswa::where('status', 'aktif')->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))->orderBy('nama')->get();

        return view('modules.presensi.create', [
            'tutors' => $tutors,
            'materis' => $materis,
            'siswas' => $siswas,
            'cabangId' => $cabangId,
        ]);
    }

    public function storeSesi(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tutor_id' => 'required|exists:tutors,id',
            'materi_les_id' => 'required|exists:materi_les,id',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'statuses' => 'required|array',
            'statuses.*' => 'required|in:hadir,izin,alfa,sakit',
        ]);

        $user = $request->user();
        $cabangId = $this->actorCabangId($request);

        $tutor = Tutor::find($validated['tutor_id']);
        $cabangToUse = $cabangId ?: $tutor->cabang_id;

        DB::transaction(function () use ($validated, $user, $cabangToUse) {
            foreach ($validated['statuses'] as $studentId => $status) {
                Kehadiran::updateOrCreate(
                    [
                        'student_id' => (int) $studentId,
                        'tutor_id' => $validated['tutor_id'],
                        'materi_les_id' => $validated['materi_les_id'],
                        'tanggal' => $validated['tanggal'],
                        'jam_mulai' => $validated['jam_mulai'],
                        'jam_selesai' => $validated['jam_selesai'],
                    ],
                    [
                        'cabang_id' => $cabangToUse,
                        'status' => $status,
                        'created_by' => $user->id,
                    ]
                );
            }
        });

        return redirect()->route('presensi.index')->with('status', 'Presensi kelas berhasil dicatat.');
    }

    public function export(Request $request): StreamedResponse
    {
        $cabangId = $this->actorCabangId($request);
        
        $rows = Kehadiran::query()
            ->with(['siswa', 'tutor', 'materiLes', 'creator', 'cabang'])
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->when($request->filled('tanggal'), fn($q) => $q->whereDate('tanggal', $request->date('tanggal')))
            ->when($request->filled('materi_les_id'), fn($q) => $q->where('materi_les_id', $request->materi_les_id))
            ->latest('tanggal')
            ->get();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Jam', 'Nama Siswa', 'Materi', 'Tutor', 'Status', 'Dicatat oleh']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    optional($row->tanggal)->format('Y-m-d'),
                    substr($row->jam_mulai, 0, 5) . '-' . substr($row->jam_selesai, 0, 5),
                    optional($row->siswa)->nama,
                    optional($row->materiLes)->nama_materi,
                    optional($row->tutor)->nama,
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
                ->select('id', 'nama')
                ->get()
        );
    }

    public function printCard(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:siswas,id',
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer',
        ]);

        $siswa = Siswa::with(['cabang', 'materiLes'])->findOrFail($request->student_id);
        
        // Logic for Jatuh Tempo from created_at
        $jatuhTempoTgl = $siswa->created_at?->day ?? 1;

        $targetDate = \Carbon\Carbon::createFromDate($request->tahun, $request->bulan, 1);
        
        $monthsData = [];
        // Loop for 6 months (selected month first, then 5 back)
        for ($i = 0; $i <= 5; $i++) {
            $current = $targetDate->copy()->subMonths($i);
            $month = $current->month;
            $year = $current->year;
            $period = $current->format('Y-m');

            // Fetch attendance for this month
            $kehadirans = Kehadiran::where('student_id', $siswa->id)
                ->whereYear('tanggal', $year)
                ->whereMonth('tanggal', $month)
                ->where('status', 'hadir')
                ->get();
            
            $presenceDays = $kehadirans->pluck('tanggal')->map(fn($d) => $d->day)->toArray();

            // Get SPP payment for this month (using invoice_period)
            $payment = Payment::where('student_id', $siswa->id)
                ->where('invoice_period', $period)
                ->whereHas('fee', function($q) {
                    $q->where('tipe', 'bulanan');
                })
                ->orderByDesc('id')
                ->first();

            $monthsData[] = [
                'monthName' => $current->translatedFormat('F'),
                'presenceDays' => $presenceDays,
                'payment' => $payment,
            ];
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.kartu-absensi', [
            'siswa' => $siswa,
            'jatuhTempoTgl' => $jatuhTempoTgl,
            'monthsData' => $monthsData,
        ]);

        return $pdf->stream("Kartu-Absensi-{$siswa->nama}.pdf");
    }
}
