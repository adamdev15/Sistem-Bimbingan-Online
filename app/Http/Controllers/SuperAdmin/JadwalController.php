<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Jadwal;
use App\Services\Notifications\InAppBellNotifier;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'tutor_id' => ['required', 'exists:tutors,id'],
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
            'tutor_id' => ['required', 'exists:tutors,id'],
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
