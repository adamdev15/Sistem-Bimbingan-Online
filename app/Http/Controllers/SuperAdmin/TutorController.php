<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Tutor;
use App\Models\User;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TutorController extends Controller
{
    public function __construct(private readonly ManagementService $service) {}

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
        $tutor->load(['cabang', 'jadwals.mataPelajaran']);

        return view('modules.tutor.show', compact('tutor'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'form_context' => ['nullable', 'string', 'max:40'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:tutors,email', 'unique:users,email'],
            'nik' => ['nullable', 'string', 'max:30', 'unique:tutors,nik'],
            'no_hp' => ['required', 'string', 'max:25'],
            'alamat' => ['required', 'string'],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'login_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $this->forceCabangForAdmin($data);

        $tutor = DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => $data['login_password'],
                'email_verified_at' => now(),
            ]);
            $user->syncRoles(['tutor']);

            return Tutor::query()->create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'nik' => $data['nik'] ?? null,
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'],
                'cabang_id' => $data['cabang_id'],
                'status' => $data['status'],
                'user_id' => $user->id,
            ]);
        });

        return $this->respondMutation($request, 'Tutor dan akun login berhasil ditambahkan.', $tutor);
    }

    public function update(Request $request, Tutor $tutor): RedirectResponse|JsonResponse
    {
        $this->guardCabangScope($tutor->cabang_id);
        $linkedUserId = $tutor->user_id;

        $userEmailUnique = Rule::unique('users', 'email');
        if ($linkedUserId) {
            $userEmailUnique = $userEmailUnique->ignore($linkedUserId);
        }

        $data = $request->validate([
            'form_context' => ['nullable', 'string', 'max:40'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('tutors', 'email')->ignore($tutor->id),
                $userEmailUnique,
            ],
            'nik' => ['nullable', 'string', 'max:30', 'unique:tutors,nik,'.$tutor->id],
            'no_hp' => ['required', 'string', 'max:25'],
            'alamat' => ['required', 'string'],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'login_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        $this->forceCabangForAdmin($data);

        DB::transaction(function () use ($tutor, $data, $linkedUserId): void {
            $tutor->update([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'nik' => $data['nik'] ?? null,
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'],
                'cabang_id' => $data['cabang_id'],
                'status' => $data['status'],
            ]);

            if ($linkedUserId) {
                $user = User::query()->find($linkedUserId);
                if ($user) {
                    $user->name = $data['nama'];
                    $user->email = $data['email'];
                    if (! empty($data['login_password'])) {
                        $user->password = $data['login_password'];
                    }
                    $user->save();
                }
            }
        });

        $tutor->refresh();

        return $this->respondMutation($request, 'Tutor berhasil diperbarui.', $tutor);
    }

    public function destroy(Request $request, Tutor $tutor): RedirectResponse|JsonResponse
    {
        $this->guardCabangScope($tutor->cabang_id);
        $userId = $tutor->user_id;

        DB::transaction(function () use ($tutor, $userId): void {
            if ($userId) {
                User::query()->whereKey($userId)->delete();
            }
            $tutor->delete();
        });

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
