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
            'materiLes' => \App\Models\MateriLes::all(),
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
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'asal_sekolah' => ['nullable', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:50', 'unique:siswas,nis'],
            'materi_les_id' => ['nullable', 'exists:materi_les,id'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'tempat_lahir_ayah' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir_ayah' => ['nullable', 'date'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'tempat_lahir_ibu' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir_ibu' => ['nullable', 'date'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:255'],
            'no_hp_orang_tua' => ['nullable', 'string', 'max:30'],
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

            $newSiswa = Siswa::query()->create([
                'nama' => $data['nama'],
                'email' => $data['email'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'nik' => $data['nik'] ?? null,
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'],
                'cabang_id' => $data['cabang_id'],
                'status' => $data['status'],
                'user_id' => $user->id,
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'asal_sekolah' => $data['asal_sekolah'] ?? null,
                'nis' => $data['nis'] ?? null,
                'materi_les_id' => $data['materi_les_id'] ?? null,
                'nama_ayah' => $data['nama_ayah'] ?? null,
                'tempat_lahir_ayah' => $data['tempat_lahir_ayah'] ?? null,
                'tanggal_lahir_ayah' => $data['tanggal_lahir_ayah'] ?? null,
                'pekerjaan_ayah' => $data['pekerjaan_ayah'] ?? null,
                'nama_ibu' => $data['nama_ibu'] ?? null,
                'tempat_lahir_ibu' => $data['tempat_lahir_ibu'] ?? null,
                'tanggal_lahir_ibu' => $data['tanggal_lahir_ibu'] ?? null,
                'pekerjaan_ibu' => $data['pekerjaan_ibu'] ?? null,
                'no_hp_orang_tua' => $data['no_hp_orang_tua'] ?? null,
            ]);

            if ($newSiswa->materi_les_id) {
                $materiLes = \App\Models\MateriLes::with('fee')->find($newSiswa->materi_les_id);
                if ($materiLes) {
                    $now = now();
                    
                    if ($materiLes->biaya_daftar > 0) {
                        $feeDaftar = \App\Models\Fee::firstOrCreate(
                            ['nama_biaya' => 'Biaya Pendaftaran - ' . $materiLes->nama_materi, 'tipe' => 'sekali'],
                            ['nominal' => $materiLes->biaya_daftar]
                        );

                        \App\Models\Payment::create([
                            'order_id' => 'REG-' . time() . rand(1000, 9999),
                            'student_id' => $newSiswa->id,
                            'biaya_id' => $feeDaftar->id,
                            'nominal' => $materiLes->biaya_daftar,
                            'tanggal_bayar' => $now->format('Y-m-d'),
                            'due_date' => $now->copy()->addDays(7)->format('Y-m-d'),
                            'tanggal_jatuh_tempo' => $now->copy()->addDays(7)->format('Y-m-d'),
                            'status' => 'belum',
                            'catatan' => 'Tagihan otomatis untuk Pendaftaran Biaya Awal.',
                        ]);
                    }

                    if ($materiLes->fee_id) {
                        \App\Models\Payment::create([
                            'order_id' => 'SPP-' . time() . rand(1000, 9999),
                            'student_id' => $newSiswa->id,
                            'biaya_id' => $materiLes->fee_id,
                            'invoice_period' => $now->format('Y-m'),
                            'nominal' => $materiLes->fee->nominal ?? 0,
                            'tanggal_bayar' => $now->format('Y-m-d'),
                            'due_date' => $now->copy()->addDays(7)->format('Y-m-d'),
                            'tanggal_jatuh_tempo' => $now->copy()->addDays(7)->format('Y-m-d'),
                            'status' => 'belum',
                            'catatan' => 'Tagihan otomatis untuk SPP Bulan Pertama.',
                        ]);
                    }
                }
            }

            return $newSiswa;
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
            'status' => ['required', 'in:aktif,nonaktif,cuti'],
            'login_password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'asal_sekolah' => ['nullable', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:50', 'unique:siswas,nis,'.$siswa->id],
            'materi_les_id' => ['nullable', 'exists:materi_les,id'],
            'nama_ayah' => ['nullable', 'string', 'max:255'],
            'tempat_lahir_ayah' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir_ayah' => ['nullable', 'date'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:255'],
            'nama_ibu' => ['nullable', 'string', 'max:255'],
            'tempat_lahir_ibu' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir_ibu' => ['nullable', 'date'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:255'],
            'no_hp_orang_tua' => ['nullable', 'string', 'max:30'],
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
                'tempat_lahir' => $data['tempat_lahir'] ?? null,
                'tanggal_lahir' => $data['tanggal_lahir'] ?? null,
                'asal_sekolah' => $data['asal_sekolah'] ?? null,
                'nis' => $data['nis'] ?? null,
                'materi_les_id' => $data['materi_les_id'] ?? null,
                'nama_ayah' => $data['nama_ayah'] ?? null,
                'tempat_lahir_ayah' => $data['tempat_lahir_ayah'] ?? null,
                'tanggal_lahir_ayah' => $data['tanggal_lahir_ayah'] ?? null,
                'pekerjaan_ayah' => $data['pekerjaan_ayah'] ?? null,
                'nama_ibu' => $data['nama_ibu'] ?? null,
                'tempat_lahir_ibu' => $data['tempat_lahir_ibu'] ?? null,
                'tanggal_lahir_ibu' => $data['tanggal_lahir_ibu'] ?? null,
                'pekerjaan_ibu' => $data['pekerjaan_ibu'] ?? null,
                'no_hp_orang_tua' => $data['no_hp_orang_tua'] ?? null,
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

            // [LOGIC] Jika status berubah menjadi cuti, tunda tanggal_jatuh_tempo pembayaran yang belum lunas
            if ($data['status'] === 'cuti') {
                \App\Models\Payment::where('student_id', $siswa->id)
                    ->where('status', 'belum')
                    ->each(function ($payment) {
                        if ($payment->tanggal_jatuh_tempo) {
                            $oldDate = \Carbon\Carbon::parse($payment->tanggal_jatuh_tempo);
                            // Tunda 30 hari sebagai default atau sesuaikan kebutuhan
                            $payment->update([
                                'tanggal_jatuh_tempo' => $oldDate->addMonth()->format('Y-m-d'),
                                'due_date' => $oldDate->format('Y-m-d'), // Sinkronkan due_date
                                'catatan' => ($payment->catatan ? $payment->catatan . "\n" : "") . "Penundaan otomatis karena status siswa CUTI."
                            ]);
                        }
                    });
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
