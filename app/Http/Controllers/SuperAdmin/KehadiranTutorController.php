<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\KehadiranTutor;
use App\Models\Tutor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class KehadiranTutorController extends Controller
{
    private function actorCabangId(Request $request): ?int
    {
        $user = $request->user();
        if ($user->hasRole('admin_cabang')) {
            return Cabang::query()->where('user_id', $user->id)->value('id') ?: 0;
        }
        return null;
    }

    public function index(Request $request)
    {
        $cabangId = $this->actorCabangId($request);
        $user = $request->user();

        $query = KehadiranTutor::with(['tutor', 'cabang', 'creator'])
            ->latest('tanggal');

        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        } elseif ($request->filled('cabang_id')) {
            $query->where('cabang_id', $request->cabang_id);
        }

        if ($request->filled('month')) {
            $m = \Carbon\Carbon::parse($request->month);
            $query->whereMonth('tanggal', $m->month)
                  ->whereYear('tanggal', $m->year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('tutor', function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%");
            });
        }

        $kehadirans = $query->paginate(15)->withQueryString();
        $cabangs = collect();
        if ($user->hasRole('super_admin')) {
            $cabangs = Cabang::all();
        }

        $tutors = Tutor::where('status', 'aktif')
            ->with('cabang:id,nama_cabang')
            ->select('id', 'nama', 'cabang_id', 'jenis_tutor')
            ->when($cabangId, fn($q) => $q->where('cabang_id', $cabangId))
            ->get();

        return view('modules.kehadiran-tutor.index', compact('kehadirans', 'cabangs', 'tutors', 'cabangId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cabang_id' => 'required|exists:cabangs,id',
            'tutor_ids' => 'required|array',
            'tutor_ids.*' => 'exists:tutors,id',
            'tanggal' => 'required|date',
            'kehadiran' => 'required|in:full,pagi_siang,siang_sore,kelas_malam',
            'jam_mulai' => 'required',
            'jam_selesai' => 'nullable',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'catatan' => 'nullable|string',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($validated, $user) {
            foreach ($validated['tutor_ids'] as $tutorId) {
                KehadiranTutor::create([
                    'cabang_id' => $validated['cabang_id'],
                    'tutor_id' => $tutorId,
                    'tanggal' => $validated['tanggal'],
                    'kehadiran' => $validated['kehadiran'],
                    'jam_mulai' => $validated['jam_mulai'],
                    'jam_selesai' => $validated['jam_selesai'],
                    'status' => $validated['status'],
                    'catatan' => $validated['catatan'],
                    'created_by' => $user->id,
                ]);
            }
        });

        return back()->with('status', 'Absensi tutor berhasil disimpan.');
    }

    public function update(Request $request, KehadiranTutor $kehadiranTutor)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kehadiran' => 'required|in:full,pagi_siang,siang_sore,kelas_malam',
            'jam_mulai' => 'required',
            'jam_selesai' => 'nullable',
            'status' => 'required|in:hadir,izin,sakit,alpha',
            'catatan' => 'nullable|string',
        ]);

        $kehadiranTutor->update($validated);

        return back()->with('status', 'Absensi tutor berhasil diperbarui.');
    }

    public function destroy(KehadiranTutor $kehadiranTutor)
    {
        $kehadiranTutor->delete();
        return back()->with('status', 'Absensi tutor berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => 'required|in:excel,pdf',
            'month' => 'required',
            'cabang_id' => 'nullable|exists:cabangs,id',
        ]);

        $cabangId = $this->actorCabangId($request);
        if ($request->user()->hasRole('super_admin') && $request->filled('cabang_id')) {
            $cabangId = $request->cabang_id;
        }

        $m = \Carbon\Carbon::parse($request->month);
        $month = $m->month;
        $year = $m->year;

        $query = KehadiranTutor::with(['tutor', 'cabang'])
            ->whereMonth('tanggal', $month)
            ->whereYear('tanggal', $year)
            ->where('status', 'hadir');

        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        }

        $data = $query->get()->groupBy(['cabang_id', 'tutor_id'])->map(function ($cabangGroup) {
            return $cabangGroup->map(function ($tutorGroup) {
                return [
                    'tutor' => $tutorGroup->first()->tutor->nama,
                    'cabang' => $tutorGroup->first()->cabang->nama_cabang,
                    'full' => $tutorGroup->where('kehadiran', 'full')->count(),
                    'pagi_siang' => $tutorGroup->where('kehadiran', 'pagi_siang')->count(),
                    'siang_sore' => $tutorGroup->where('kehadiran', 'siang_sore')->count(),
                    'kelas_malam' => $tutorGroup->where('kehadiran', 'kelas_malam')->count(),
                ];
            });
        });

        $period = $m->translatedFormat('F Y');
        $cabangName = $cabangId ? Cabang::find($cabangId)->nama_cabang : 'Semua Cabang';

        if ($request->type === 'pdf') {
            $pdf = Pdf::loadView('reports.kehadiran-tutor', compact('data', 'period', 'cabangName'));
            return $pdf->stream("Laporan-Kehadiran-Tutor-{$period}.pdf");
        }

        // For Excel, we'll use a simple collection export or just manual CSV for now if Excel library isn't set up for views
        // But usually people want real Excel. I'll use a temporary class if needed.
        return Excel::download(new class($data, $period, $cabangName) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithMapping {
            private $data, $period, $cabangName;
            public function __construct($data, $period, $cabangName) {
                $this->data = $data; $this->period = $period; $this->cabangName = $cabangName;
            }
            public function collection() {
                $rows = collect();
                foreach($this->data as $cGroup) {
                    foreach($cGroup as $row) {
                        $rows->push($row);
                    }
                }
                return $rows;
            }
            public function headings(): array {
                return ['Periode', 'Cabang', 'Nama Tutor', 'Full', 'Pagi-Siang', 'Siang-Sore', 'Kelas Malam'];
            }
            public function map($row): array {
                return [
                    $this->period,
                    $row['cabang'],
                    $row['tutor'],
                    $row['full'] . 'X',
                    $row['pagi_siang'] . 'X',
                    $row['siang_sore'] . 'X',
                    $row['kelas_malam'] . 'X',
                ];
            }
        }, "Laporan-Kehadiran-Tutor-{$period}.xlsx");
    }
}
