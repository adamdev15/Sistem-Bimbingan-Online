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
    public function __construct(
        private readonly ManagementService $service,
        private readonly \App\Services\WhatsApp\WhatsAppNotifier $whatsapp,
    ) {}

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
            'email' => ['nullable', 'email', 'unique:siswas,email'],
            'jenis_kelamin' => ['required', 'in:laki_laki,perempuan'],
            'nik' => ['nullable', 'string', 'max:30', 'unique:siswas,nik'],
            'no_hp' => ['required', 'string', 'max:25'],
            'alamat' => ['required', 'string'],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'status' => ['required', 'in:aktif,nonaktif'],
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
            $newSiswa = Siswa::query()->create([
                'nama' => $data['nama'],
                'email' => $data['email'] ?? null,
                'jenis_kelamin' => $data['jenis_kelamin'],
                'nik' => $data['nik'] ?? null,
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'],
                'cabang_id' => $data['cabang_id'],
                'status' => $data['status'],
                'user_id' => null, // No longer linked to a User account by default
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
                $materiLes = \App\Models\MateriLes::find($newSiswa->materi_les_id);
                if ($materiLes) {
                    $now = now();
                    
                    if ($materiLes->biaya_daftar > 0) {
                        $paymentReg = \App\Models\Payment::create([
                            'order_id' => 'REG-' . time() . rand(1000, 9999),
                            'student_id' => $newSiswa->id,
                            'biaya_id' => 2, // Pendaftaran Bimbel Jarimatrik
                            'invoice_period' => $now->format('Y-m'),
                            'nominal' => $materiLes->biaya_daftar,
                            'tanggal_bayar' => $now->format('Y-m-d'),
                            'due_date' => $now->format('Y-m-d'),
                            'tanggal_jatuh_tempo' => $now->format('Y-m-d'),
                            'status' => 'belum',
                            'catatan' => 'Tagihan otomatis untuk Pendaftaran Biaya Awal.',
                        ]);
                        $this->whatsapp->notifySiswaInvoiceCreated($paymentReg);
                    }

                    if ($materiLes->biaya_spp > 0) {
                        $paymentSpp = \App\Models\Payment::create([
                            'order_id' => 'SPP-' . time() . rand(1000, 9999),
                            'student_id' => $newSiswa->id,
                            'biaya_id' => 9, // SPP Bulanan Bimbel Jarimatrik
                            'invoice_period' => $now->format('Y-m'),
                            'nominal' => $materiLes->biaya_spp,
                            'tanggal_bayar' => $now->format('Y-m-d'),
                            'due_date' => $now->format('Y-m-d'),
                            'tanggal_jatuh_tempo' => $now->format('Y-m-d'),
                            'status' => 'belum',
                            'catatan' => 'Tagihan otomatis untuk SPP Bulan Pertama.',
                        ]);
                        $this->whatsapp->notifySiswaInvoiceCreated($paymentSpp);
                    }
                }
            }

            return $newSiswa;
        });

        return $this->respondMutation($request, 'Siswa berhasil ditambahkan.', $siswa);
    }

    public function update(Request $request, Siswa $siswa): RedirectResponse|JsonResponse
    {
        $this->guardCabangScope($siswa->cabang_id);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('siswas', 'email')->ignore($siswa->id)],
            'jenis_kelamin' => ['required', 'in:laki_laki,perempuan'],
            'nik' => ['nullable', 'string', 'max:30', 'unique:siswas,nik,'.$siswa->id],
            'no_hp' => ['required', 'string', 'max:25'],
            'alamat' => ['required', 'string'],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'status' => ['required', 'in:aktif,nonaktif,cuti'],
            'cuti_sampai' => ['nullable', 'date', 'required_if:status,cuti'],
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
            'tanggal_daftar' => ['nullable', 'date'],
        ]);
        $this->forceCabangForAdmin($data);

        DB::transaction(function () use ($siswa, $data, $request): void {
            $siswa->update([
                'nama' => $data['nama'],
                'email' => $data['email'] ?? null,
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
                'cuti_sampai' => $data['cuti_sampai'] ?? null,
            ]);

            if ($request->filled('tanggal_daftar')) {
                $siswa->created_at = $data['tanggal_daftar'];
                $siswa->save();
            }

            // Jika status berubah menjadi cuti, tunda tanggal_jatuh_tempo pembayaran yang belum lunas
            if ($data['status'] === 'cuti' && !empty($data['cuti_sampai'])) {
                // Update created_at untuk mengubah anchor jatuh tempo bulan depan
                $siswa->created_at = $data['cuti_sampai'];
                $siswa->save();

                \App\Models\Payment::where('student_id', $siswa->id)
                    ->where('status', 'belum')
                    ->each(function ($payment) use ($data) {
                        $payment->update([
                            'tanggal_jatuh_tempo' => $data['cuti_sampai'],
                            'due_date' => $data['cuti_sampai'],
                            'catatan' => ($payment->catatan ? $payment->catatan . "\n" : "") . "Penundaan otomatis sampai akhir cuti: " . $data['cuti_sampai']
                        ]);
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

    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $user = auth()->user();
        $cabangId = $user->hasRole('admin_cabang') 
            ? \App\Models\Cabang::where('user_id', $user->id)->value('id') 
            : null;

        $query = Siswa::query()
            ->with(['cabang', 'materiLes'])
            ->when($cabangId, fn ($q) => $q->where('cabang_id', $cabangId))
            ->when(! $cabangId && $request->filled('cabang_id'), fn ($q) => $q->where('cabang_id', $request->integer('cabang_id')))
            ->when($request->string('search')->toString(), function ($q, $search) {
                $q->where(function ($w) use ($search) {
                    $w->where('nama', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->latest('id');

        $name = 'siswa-export-' . now()->format('Y-m-d-His') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\SiswaExport($query), $name);
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
