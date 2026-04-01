<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CabangController extends Controller
{
    public function __construct(private readonly ManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('modules.cabang.index', [
            'cabangs' => $this->service->cabangIndex($request),
            'filters' => $request->only(['search', 'kota', 'active_only']),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'nama_cabang' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'kota' => ['required', 'string', 'max:120'],
            'telepon' => ['nullable', 'string', 'max:25'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        $cabang = Cabang::create($data);

        return $this->respondMutation($request, 'Cabang berhasil ditambahkan.', $cabang);
    }

    public function update(Request $request, Cabang $cabang): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'nama_cabang' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'kota' => ['required', 'string', 'max:120'],
            'telepon' => ['nullable', 'string', 'max:25'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        $cabang->update($data);

        return $this->respondMutation($request, 'Cabang berhasil diperbarui.', $cabang);
    }

    public function destroy(Request $request, Cabang $cabang): RedirectResponse|JsonResponse
    {
        $cabang->delete();

        return $this->respondMutation($request, 'Cabang berhasil dihapus.');
    }

    private function respondMutation(Request $request, string $message, ?Cabang $cabang = null): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $cabang,
            ]);
        }

        return back()->with('status', $message);
    }
}
