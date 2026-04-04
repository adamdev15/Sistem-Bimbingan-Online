<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Siswa;
use App\Models\User;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiswaController extends Controller
{
    public function __construct(private readonly ManagementService $service) {}

    public function index(Request $request): View
    {
        return view('modules.siswa.index', [
            'siswas' => $this->service->siswaIndex($request),
            'cabangs' => $this->service->cabangForSelect(),
            'filters' => $request->only(['search', 'cabang_id']),
        ]);
    }

    public function show(Siswa $siswa): View
    {
        $this->guardCabangScope($siswa->cabang_id);
        $siswa->load(['cabang', 'payments.fee']);

        return view('modules.siswa.show', compact('siswa'));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:siswas,email', 'unique:users,email'],
            'jenis_kelamin' => ['required', 'in:laki_laki,perempuan'],
            'nik' => ['nullable', 'string', 'max:30', 'unique:siswas,nik'],
            'no_hp' => ['required', 'string', 'max:25'],
            'alamat' => ['required', 'string'],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'login_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $this->forceCabangForAdmin($data);

        $siswa = DB::transaction(function () use ($data) {
            $user = User::query()->create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => $data['login_password'],
                'email_verified_at' => now(),
            ]);
            $user->syncRoles(['siswa']);

            return Siswa::query()->create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'nik' => $data['nik'] ?? null,
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'],
                'cabang_id' => $data['cabang_id'],
                'status' => $data['status'],
                'user_id' => $user->id,
            ]);
        });

        return $this->respondMutation($request, 'Siswa dan akun login berhasil ditambahkan.', $siswa);
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse|JsonResponse
    {
        $this->guardCabangScope($siswa->cabang_id);
        $linkedUserId = $siswa->user_id;

        $userEmailUnique = Rule::unique('users', 'email');
        if ($linkedUserId) {
            $userEmailUnique = $userEmailUnique->ignore($linkedUserId);
        }

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('siswas', 'email')->ignore($siswa->id),
                $userEmailUnique,
            ],
            'jenis_kelamin' => ['required', 'in:laki_laki,perempuan'],
            'nik' => ['nullable', 'string', 'max:30', 'unique:siswas,nik,'.$siswa->id],
            'no_hp' => ['required', 'string', 'max:25'],
            'alamat' => ['required', 'string'],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'login_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);
        $this->forceCabangForAdmin($data);

        DB::transaction(function () use ($siswa, $data, $linkedUserId): void {
            $siswa->update([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'jenis_kelamin' => $data['jenis_kelamin'],
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

        $siswa->refresh();

        return $this->respondMutation($request, 'Siswa berhasil diperbarui.', $siswa);
    }

    public function destroy(Request $request, Siswa $siswa): RedirectResponse|JsonResponse
    {
        $this->guardCabangScope($siswa->cabang_id);
        $userId = $siswa->user_id;

        DB::transaction(function () use ($siswa, $userId): void {
            if ($userId) {
                User::query()->whereKey($userId)->delete();
            }
            $siswa->delete();
        });

        return $this->respondMutation($request, 'Siswa berhasil dihapus.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $rows = $this->service->siswaIndex($request)->getCollection();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Nama', 'Email', 'Cabang', 'No HP', 'Status']);
            foreach ($rows as $row) {
                fputcsv($handle, [$row->nama, $row->email, optional($row->cabang)->nama_cabang, $row->no_hp, $row->status]);
            }
            fclose($handle);
        }, 'siswa-export.csv', ['Content-Type' => 'text/csv']);
    }

    private function respondMutation(Request $request, string $message, ?Siswa $siswa = null): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'data' => $siswa,
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
