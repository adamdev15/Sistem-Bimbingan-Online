<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Tutor;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TutorController extends Controller
{
    public function __construct(private readonly ManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('modules.tutor.index', [
            'tutors' => $this->service->tutorIndex($request),
            'cabangs' => $this->service->cabangForSelect(),
            'filters' => $request->only(['search', 'cabang_id', 'status']),
        ]);
    }

    public function show(Tutor $tutor): View
    {
        $this->guardCabangScope($tutor->cabang_id);
        $tutor->load(['cabang', 'jadwals']);

        return view('modules.tutor.show', compact('tutor'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:tutors,email'],
            'nik' => ['nullable', 'string', 'max:30', 'unique:tutors,nik'],
            'no_hp' => ['required', 'string', 'max:25'],
            'alamat' => ['required', 'string'],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);
        $this->forceCabangForAdmin($data);

        $tutor = Tutor::create($data);

        return $this->respondMutation($request, 'Tutor berhasil ditambahkan.', $tutor);
    }

    public function update(Request $request, Tutor $tutor): RedirectResponse|JsonResponse
    {
        $this->guardCabangScope($tutor->cabang_id);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:tutors,email,'.$tutor->id],
            'nik' => ['nullable', 'string', 'max:30', 'unique:tutors,nik,'.$tutor->id],
            'no_hp' => ['required', 'string', 'max:25'],
            'alamat' => ['required', 'string'],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);
        $this->forceCabangForAdmin($data);

        $tutor->update($data);

        return $this->respondMutation($request, 'Tutor berhasil diperbarui.', $tutor);
    }

    public function destroy(Request $request, Tutor $tutor): RedirectResponse|JsonResponse
    {
        $this->guardCabangScope($tutor->cabang_id);
        $tutor->delete();

        return $this->respondMutation($request, 'Tutor berhasil dihapus.');
    }

    private function respondMutation(Request $request, string $message, ?Tutor $tutor = null): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $tutor,
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

    private function forceCabangForAdmin(array &$data): void
    {
        $user = auth()->user();
        if ($user && $user->hasRole('admin_cabang')) {
            $data['cabang_id'] = Cabang::query()->where('user_id', $user->id)->value('id');
        }
    }
}
