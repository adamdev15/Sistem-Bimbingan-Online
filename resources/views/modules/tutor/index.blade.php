@php
    $openTutorCreate = $errors->any() && old('form_context') === 'tutor_create';
@endphp
<x-layouts.dashboard-shell title="Tutor - eBimbel">
    <div
        x-data="{
            createOpen: @json($openTutorCreate),
            editOpen: false,
            deleteOpen: false,
            edit: {
                id: null,
                nama: '',
                email: '',
                nik: '',
                no_hp: '',
                alamat: '',
                cabang_id: '',
                status: 'aktif',
            },
            removeId: null,
        }"
        class="space-y-6"
    >
        <x-module-page-header title="Data tutor" description="Penugasan cabang, jadwal, dan akun login portal tutor (email unik di sistem).">
            <x-slot name="actions">
                <button @click="createOpen = true" type="button" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 transition hover:bg-blue-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Tambah tutor
                </button>
            </x-slot>
        </x-module-page-header>

        @if (session('status'))
            <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                <p class="font-semibold">Periksa kembali formulir:</p>
                <ul class="mt-2 list-inside list-disc space-y-0.5">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm ring-1 ring-slate-900/5">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari</label>
                <input name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Nama / email" class="mt-1.5 min-w-[200px] rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cabang</label>
                <select name="cabang_id" class="mt-1.5 rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                    <option value="">Semua cabang</option>
                    @foreach ($cabangs as $cabang)
                        <option value="{{ $cabang->id }}" @selected(($filters['cabang_id'] ?? null) == $cabang->id)>{{ $cabang->nama_cabang }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" class="mt-1.5 rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                    <option value="">Semua</option>
                    <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(($filters['status'] ?? '') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Terapkan</button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3.5">Kode</th>
                            <th class="px-4 py-3.5">Nama</th>
                            <th class="px-4 py-3.5">Email login</th>
                            <th class="px-4 py-3.5">Cabang</th>
                            <th class="px-4 py-3.5">Sesi/minggu</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($tutors as $tutor)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-3.5 font-mono text-xs text-slate-600">T-{{ str_pad((string) $tutor->id, 3, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-4 py-3.5 font-medium text-slate-900">{{ $tutor->nama }}</td>
                                <td class="px-4 py-3.5 text-slate-600">{{ optional($tutor->user)->email ?? $tutor->email }}</td>
                                <td class="px-4 py-3.5">{{ optional($tutor->cabang)->nama_cabang }}</td>
                                <td class="px-4 py-3.5">{{ $tutor->jadwals_count }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $tutor->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">{{ ucfirst($tutor->status) }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-3">
                                        <a href="{{ route('tutors.show', $tutor) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Profil</a>
                                        <button
                                            type="button"
                                            @click="editOpen = true; edit = { id: {{ $tutor->id }}, nama: @js($tutor->nama), email: @js($tutor->email), nik: @js($tutor->nik), no_hp: @js($tutor->no_hp), alamat: @js($tutor->alamat), cabang_id: '{{ $tutor->cabang_id }}', status: @js($tutor->status) }"
                                            class="text-sm font-semibold text-blue-600 hover:text-blue-800"
                                        >
                                            Edit
                                        </button>
                                        <button type="button" @click="deleteOpen = true; removeId = {{ $tutor->id }}" class="text-sm font-semibold text-rose-600 hover:text-rose-800">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-500">Belum ada data tutor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">{{ $tutors->links() }}</div>
        </div>

        {{-- Modal: create --}}
        <div x-show="createOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true">
            <div @click.outside="createOpen = false" @keydown.escape.window="createOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-slate-900">Tambah tutor</h3>
                        <p class="mt-1 text-sm text-slate-500">Email dipakai untuk login dan harus belum terdaftar sebagai pengguna.</p>
                    </div>
                    <button type="button" @click="createOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('tutors.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="form_context" value="tutor_create">
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Nama</label>
                            <input name="nama" value="{{ old('form_context') === 'tutor_create' ? old('nama') : '' }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Email (login)</label>
                            <input name="email" type="email" value="{{ old('form_context') === 'tutor_create' ? old('email') : '' }}" required autocomplete="email" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Kata sandi</label>
                            <input name="login_password" type="password" required autocomplete="new-password" minlength="8" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Konfirmasi kata sandi</label>
                            <input name="login_password_confirmation" type="password" required autocomplete="new-password" minlength="8" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">NIK</label>
                            <input name="nik" value="{{ old('form_context') === 'tutor_create' ? old('nik') : '' }}" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">No HP</label>
                            <input name="no_hp" value="{{ old('form_context') === 'tutor_create' ? old('no_hp') : '' }}" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Cabang</label>
                            <select name="cabang_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                @forelse ($cabangs as $cabang)
                                    <option value="{{ $cabang->id }}" @selected(old('form_context') === 'tutor_create' && (string) old('cabang_id') === (string) $cabang->id)>{{ $cabang->nama_cabang }}</option>
                                @empty
                                    <option value="" disabled selected>Belum ada cabang ù buat cabang dulu</option>
                                @endforelse
                            </select>
                            @if ($cabangs->isEmpty())
                                <p class="mt-1.5 text-xs text-amber-700">Tambah data cabang di menu Cabang sebelum menambah tutor.</p>
                            @endif
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Status</label>
                            <select name="status" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="aktif" @selected(old('form_context') !== 'tutor_create' || old('status', 'aktif') === 'aktif')>Aktif</option>
                                <option value="nonaktif" @selected(old('form_context') === 'tutor_create' && old('status') === 'nonaktif')>Nonaktif</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Alamat</label>
                            <textarea name="alamat" required rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">{{ old('form_context') === 'tutor_create' ? old('alamat') : '' }}</textarea>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                        <button type="button" @click="createOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: edit --}}
        <div x-show="editOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true">
            <div @click.outside="editOpen = false" @keydown.escape.window="editOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-slate-900">Edit tutor</h3>
                        <p class="mt-1 text-sm text-slate-500">Perubahan email mengikuti akun login. Kosongkan kata sandi jika tidak diubah.</p>
                    </div>
                    <button type="button" @click="editOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" :action="`{{ url('/tutors') }}/${edit.id}`" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Nama</label>
                            <input name="nama" x-model="edit.nama" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Email (login)</label>
                            <input name="email" type="email" x-model="edit.email" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Kata sandi baru (opsional)</label>
                            <input name="login_password" type="password" autocomplete="new-password" minlength="8" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Konfirmasi</label>
                            <input name="login_password_confirmation" type="password" autocomplete="new-password" minlength="8" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">NIK</label>
                            <input name="nik" x-model="edit.nik" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">No HP</label>
                            <input name="no_hp" x-model="edit.no_hp" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Cabang</label>
                            <select name="cabang_id" x-model="edit.cabang_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                @foreach ($cabangs as $cabang)
                                    <option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Status</label>
                            <select name="status" x-model="edit.status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Alamat</label>
                            <textarea name="alamat" x-model="edit.alamat" required rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15"></textarea>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                        <button type="button" @click="editOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: delete --}}
        <div x-show="deleteOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true">
            <div @click.outside="deleteOpen = false" @keydown.escape.window="deleteOpen = false" class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                <h3 class="text-lg font-bold text-slate-900">Hapus tutor</h3>
                <p class="mt-2 text-sm text-slate-600">Tutor dan akun login terkait akan dihapus.</p>
                <form method="POST" :action="`{{ url('/tutors') }}/${removeId}`" class="mt-6 flex flex-wrap justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard-shell>
