<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Spatie\Permission\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $cabangs = \App\Models\Cabang::all();
        $materiLes = \App\Models\MateriLes::all();
        $settings = \App\Models\Setting::pluck('value', 'setting_key')->toArray();
        return view('auth.register-wizard', compact('cabangs', 'materiLes', 'settings'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            // Step 2: Data Siswa (Account + Siswa profile)
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class, 'unique:siswas,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'jenis_kelamin' => ['required', 'in:laki_laki,perempuan'],
            'no_hp' => ['required', 'string', 'max:25'],
            'alamat' => ['required', 'string'],
            'cabang_id' => ['required', 'exists:cabangs,id'],
            'tempat_lahir' => ['nullable', 'string', 'max:255'],
            'tanggal_lahir' => ['nullable', 'date'],
            'asal_sekolah' => ['nullable', 'string', 'max:255'],
            'nis' => ['nullable', 'string', 'max:50', 'unique:siswas,nis'],
            'materi_les_id' => ['nullable', 'exists:materi_les,id'],
            
            // Step 3: Data Orang Tua
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

        $user = \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole(Role::firstOrCreate(['name' => 'siswa', 'guard_name' => 'web']));

            $siswa = \App\Models\Siswa::create([
                'user_id' => $user->id,
                'nama' => $data['name'],
                'email' => $data['email'],
                'jenis_kelamin' => $data['jenis_kelamin'],
                'no_hp' => $data['no_hp'],
                'alamat' => $data['alamat'],
                'cabang_id' => $data['cabang_id'],
                'status' => 'aktif',
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

            if ($siswa->materi_les_id) {
                $materiLes = \App\Models\MateriLes::with('fee')->find($siswa->materi_les_id);
                if ($materiLes) {
                    $now = now();
                    
                    // 1. Tagihan Pendaftaran
                    if ($materiLes->biaya_daftar > 0) {
                        $feeDaftar = \App\Models\Fee::firstOrCreate(
                            ['nama_biaya' => 'Biaya Pendaftaran - ' . $materiLes->nama_materi, 'tipe' => 'sekali'],
                            ['nominal' => $materiLes->biaya_daftar]
                        );

                        \App\Models\Payment::create([
                            'order_id' => 'REG-' . time() . rand(1000, 9999),
                            'student_id' => $siswa->id,
                            'biaya_id' => $feeDaftar->id,
                            'nominal' => $materiLes->biaya_daftar,
                            'tanggal_bayar' => $now->format('Y-m-d'),
                            'due_date' => $now->copy()->addDays(7)->format('Y-m-d'),
                            'tanggal_jatuh_tempo' => $now->copy()->addDays(7)->format('Y-m-d'),
                            'status' => 'belum',
                            'catatan' => 'Tagihan otomatis untuk Pendaftaran Biaya Awal.',
                        ]);
                    }

                    // 2. Tagihan SPP Bulan Pertama
                    if ($materiLes->fee_id) {
                        \App\Models\Payment::create([
                            'order_id' => 'SPP-' . time() . rand(1000, 9999),
                            'student_id' => $siswa->id,
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

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
