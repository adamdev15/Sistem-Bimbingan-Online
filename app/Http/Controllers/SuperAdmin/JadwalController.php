<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Jadwal;
use App\Models\Siswa;
use App\Services\Notifications\InAppBellNotifier;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JadwalController extends Controller
{
    public function __construct(private readonly ManagementService $service) {}

    public function index(Request $request): View
    {
        return view('modules.jadwal.index', [
            'jadwals' => $this->service->jadwalIndex($request),
            'cabangs' => $this->service->cabangForSelect(),
            'tutors' => $this->service->tutorsForSelect(),
            'mataPelajarans' => $this->service->mataPelajaranForSelect(),
            'filters' => $request->only(['cabang_id', 'hari']),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'tutor_id' => [
                'required',
                Rule::exists('tutors', 'id')->where(fn ($q) => $q->where('cabang_id', $request->integer('cabang_id'))),
            ],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajarans,id'],
            'hari' => ['required', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);

        $jadwal = Jadwal::create($data);

        app(InAppBellNotifier::class)->jadwalCreated($jadwal);

        return $this->respondMutation($request, 'Jadwal berhasil ditambahkan.', $jadwal);
    }

    public function update(Request $request, Jadwal $jadwal): RedirectResponse|JsonResponse
    {
        $this->guardCabangScope($jadwal->cabang_id);
        $data = $request->validate([
            'tutor_id' => [
                'required',
                Rule::exists('tutors', 'id')->where(fn ($q) => $q->where('cabang_id', $request->integer('cabang_id'))),
            ],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'mata_pelajaran_id' => ['required', 'exists:mata_pelajarans,id'],
            'hari' => ['required', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);

        $jadwal->update($data);

        return $this->respondMutation($request, 'Jadwal berhasil diperbarui.', $jadwal);
    }

    public function destroy(Request $request, Jadwal $jadwal): RedirectResponse|JsonResponse
    {
        $this->guardCabangScope($jadwal->cabang_id);
        $jadwal->delete();

        return $this->respondMutation($request, 'Jadwal berhasil dihapus.');
    }

    public function peserta(Jadwal $jadwal): View
    {
        $this->guardCabangScope($jadwal->cabang_id);
        $jadwal->load(['cabang', 'mataPelajaran', 'tutor', 'siswas']);

        $siswaCandidates = Siswa::query()
            ->where('cabang_id', $jadwal->cabang_id)
            ->where('status', 'aktif')
            ->orderBy('nama')
            ->get(['id', 'nama']);

        return view('modules.jadwal.peserta', compact('jadwal', 'siswaCandidates'));
    }

    public function updatePeserta(Request $request, Jadwal $jadwal): RedirectResponse
    {
        $this->guardCabangScope($jadwal->cabang_id);

        $data = $request->validate([
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['integer', 'exists:siswas,id'],
        ]);

        $ids = collect($data['student_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($ids !== []) {
            $validCount = Siswa::query()
                ->whereIn('id', $ids)
                ->where('cabang_id', $jadwal->cabang_id)
                ->count();

            if ($validCount !== count($ids)) {
                return back()->withErrors(['student_ids' => 'Semua siswa harus dari cabang yang sama dengan kelas.'])->withInput();
            }
        }

        $jadwal->siswas()->sync($ids);

        return back()->with('status', 'Peserta kelas diperbarui.');
    }

    private function respondMutation(Request $request, string $message, ?Jadwal $jadwal = null): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $jadwal,
            ]);
        }

        return back()->with('status', $message);
    }

    private function guardCabangScope(?int $modelCabangId): void
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('admin_cabang')) {
            return;
        }

        $adminCabangId = Cabang::query()->where('user_id', $user->id)->value('id');
        if ((int) $adminCabangId !== (int) $modelCabangId) {
            abort(403);
        }
    }
}
