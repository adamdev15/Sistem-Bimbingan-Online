<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\User;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CabangController extends Controller
{
    public function __construct(private readonly ManagementService $service) {}

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
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $cabang = DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'name' => $data['admin_name'],
                'email' => $data['admin_email'],
                'password' => $data['admin_password'],
                'email_verified_at' => now(),
            ]);
            $user->syncRoles(['admin_cabang']);

            return Cabang::query()->create([
                'nama_cabang' => $data['nama_cabang'],
                'alamat' => $data['alamat'],
                'kota' => $data['kota'],
                'telepon' => $data['telepon'] ?? null,
                'status' => $data['status'],
                'user_id' => $user->id,
            ]);
        });

        return $this->respondMutation($request, 'Cabang dan akun admin cabang berhasil ditambahkan.', $cabang);
    }

    public function update(Request $request, Cabang $cabang): RedirectResponse|JsonResponse
    {
        $linkedUserId = $cabang->user_id;

        $adminEmailRule = Rule::unique('users', 'email');
        if ($linkedUserId) {
            $adminEmailRule = $adminEmailRule->ignore($linkedUserId);
        }

        $data = $request->validate([
            'nama_cabang' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'kota' => ['required', 'string', 'max:120'],
            'telepon' => ['nullable', 'string', 'max:25'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'admin_name' => ['nullable', 'string', 'max:255'],
            'admin_email' => [
                'nullable',
                'email',
                'max:255',
                $adminEmailRule,
            ],
            'admin_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($cabang, $data, $linkedUserId): void {
            $cabang->update([
                'nama_cabang' => $data['nama_cabang'],
                'alamat' => $data['alamat'],
                'kota' => $data['kota'],
                'telepon' => $data['telepon'] ?? null,
                'status' => $data['status'],
            ]);

            if ($linkedUserId) {
                $user = User::query()->find($linkedUserId);
                if ($user) {
                    if (! empty($data['admin_name'])) {
                        $user->name = $data['admin_name'];
                    }
                    if (! empty($data['admin_email'])) {
                        $user->email = $data['admin_email'];
                    }
                    if (! empty($data['admin_password'])) {
                        $user->password = $data['admin_password'];
                    }
                    $user->save();
                }
            }
        });

        $cabang->refresh();

        return $this->respondMutation($request, 'Cabang berhasil diperbarui.', $cabang);
    }

    public function destroy(Request $request, Cabang $cabang): RedirectResponse|JsonResponse
    {
        $userId = $cabang->user_id;

        DB::transaction(function () use ($cabang, $userId): void {
            if ($userId) {
                User::query()->whereKey($userId)->delete();
            }
            $cabang->delete();
        });

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
