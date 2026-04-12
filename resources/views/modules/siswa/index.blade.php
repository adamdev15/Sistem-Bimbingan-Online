<x-layouts.dashboard-shell title="Siswa - eBimbel">
    <div
        x-data="{
            createOpen: false,
            editOpen: false,
            deleteOpen: false,
            edit: {
                id: null,
                nama: '',
                email: '',
                jenis_kelamin: 'laki_laki',
                nik: '',
                no_hp: '',
                alamat: '',
                cabang_id: '',
                status: 'aktif',
                tempat_lahir: '',
                tanggal_lahir: '',
                asal_sekolah: '',
                nis: '',
                materi_les_id: '',
                nama_ayah: '',
                tempat_lahir_ayah: '',
                tanggal_lahir_ayah: '',
                pekerjaan_ayah: '',
                nama_ibu: '',
                tempat_lahir_ibu: '',
                tanggal_lahir_ibu: '',
                pekerjaan_ibu: '',
                no_hp_orang_tua: '',
            },
            removeId: null,
            printOpen: false,
            selectedSiswa: { id: null, nama: '' },
        }"
        class="space-y-6"
    >
        <x-module-page-header title="Data siswa" description="Pendaftaran siswa, cabang, dan akun login portal siswa (email unik di sistem).">
        </x-module-page-header>

        @if (session('status'))
            <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        @if ($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <ul class="list-inside list-disc text-sm text-red-600">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 p-5">

            {{-- FILTER + ACTION --}}
            <div class="flex flex-wrap items-end gap-3 p-4 border-b border-slate-100 mb-4">

                <form method="GET" class="flex flex-wrap items-end gap-3 flex-1">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari</label>
                        <input name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Nama / email / NIK" class="mt-1.5 min-w-[220px] rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cabang</label>
                        <select name="cabang_id" class="mt-1.5 rounded-xl border border-slate-200 px-6 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            <option value="">Semua cabang</option>
                            @foreach ($cabangs as $cabang)
                                <option value="{{ $cabang->id }}" @selected(($filters['cabang_id'] ?? null) == $cabang->id)>{{ $cabang->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Terapkan</button>
                </form>

                {{-- BUTTON RIGHT --}}
                <div class="flex items-center gap-2 ml-auto">
                    <a href="{{ route('siswa.export.csv', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-emerald-600/20 transition hover:bg-emerald-700">Ekspor CSV</a>
                    <button @click="createOpen = true" type="button" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 transition hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah siswa
                    </button>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3.5">No</th>
                            <th class="px-4 py-3.5">Nama</th>
                            <th class="px-4 py-3.5">Cabang</th>
                            <th class="px-4 py-3.5">Asal Sekolah</th>
                            <th class="px-4 py-3.5">Alamat</th>
                            <th class="px-4 py-3.5">Materi Les</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($siswas as $siswa)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-3.5 font-mono text-xs text-slate-600">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3.5 font-m   edium text-slate-900">{{ $siswa->nama }}</td>
                                <td class="px-4 py-3.5 text-slate-600">{{ optional($siswa->cabang)->nama_cabang }}</td>
                                <td class="px-4 py-3.5 text-slate-600">{{ $siswa->asal_sekolah }}</td>
                                <td class="px-4 py-3.5 text-slate-600">{{ $siswa->alamat }}</td>
                                <td class="px-4 py-3.5 text-slate-600">{{ optional($siswa->materiLes)->nama_materi ?? '—' }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold 
                                    {{ $siswa->status === 'aktif' 
                                        ? 'bg-emerald-100 text-emerald-800' 
                                        : ($siswa->status === 'cuti' 
                                            ? 'bg-yellow-100 text-yellow-800' 
                                            : 'bg-rose-100 text-rose-800') 
                                    }}">
                                        {{ ucfirst($siswa->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('siswa.show', $siswa) }}"
                                            class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 p-2 rounded-lg transition-colors"
                                            title="Profil">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none">
                                                <path d="M16 7C16 9.20914 14.2091 11 12 11C9.79086 11 8 9.20914 8 7C8 4.79086 9.79086 3 12 3C14.2091 3 16 4.79086 16 7Z"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                <path d="M12 14C8.13401 14 5 17.134 5 21H19C19 17.134 15.866 14 12 14Z"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>

                                        <button
                                            type="button"
                                            @click="printOpen = true; selectedSiswa = { id: {{ $siswa->id }}, nama: @js($siswa->nama) }"
                                            class="text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 p-2 rounded-lg transition-colors"
                                            title="Kartu">
                                            {{-- ICON CARD --}}
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <rect x="2" y="5" width="20" height="14" rx="2" ry="2" stroke-width="2"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2 10h20"/>
                                            </svg>
                                        </button>

                                        <button
                                            type="button"
                                            @click="editOpen = true; edit = { id: {{ $siswa->id }}, nama: @js($siswa->nama), email: @js($siswa->email), jenis_kelamin: @js($siswa->jenis_kelamin), nik: @js($siswa->nik), no_hp: @js($siswa->no_hp), alamat: @js($siswa->alamat), cabang_id: '{{ $siswa->cabang_id }}', status: @js($siswa->status), tempat_lahir: @js($siswa->tempat_lahir), tanggal_lahir: @js($siswa->tanggal_lahir), asal_sekolah: @js($siswa->asal_sekolah), nis: @js($siswa->nis), materi_les_id: '{{ $siswa->materi_les_id }}', nama_ayah: @js($siswa->nama_ayah), tempat_lahir_ayah: @js($siswa->tempat_lahir_ayah), tanggal_lahir_ayah: @js($siswa->tanggal_lahir_ayah), pekerjaan_ayah: @js($siswa->pekerjaan_ayah), nama_ibu: @js($siswa->nama_ibu), tempat_lahir_ibu: @js($siswa->tempat_lahir_ibu), tanggal_lahir_ibu: @js($siswa->tanggal_lahir_ibu), pekerjaan_ibu: @js($siswa->pekerjaan_ibu), no_hp_orang_tua: @js($siswa->no_hp_orang_tua) }"
                                            class="text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 p-2 rounded-lg transition-colors"
                                            title="Edit">
                                            {{-- ICON EDIT --}}
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="deleteOpen = true; removeId = {{ $siswa->id }}" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors" title="Hapus">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg></button>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-slate-500">Belum ada data siswa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">{{ $siswas->links() }}</div>
        </div>

        {{-- Modal: create --}}
        <div x-show="createOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true">
            <div @click.outside="createOpen = false" @keydown.escape.window="createOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-slate-900">Tambah siswa</h3>
                        <p class="mt-1 text-sm text-slate-500">Email dipakai untuk login portal siswa dan harus belum terdaftar di tabel pengguna.</p>
                    </div>
                    <button type="button" @click="createOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('siswa.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Nama</label>
                            <input name="nama" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Email (login)</label>
                            <input name="email" type="email" required autocomplete="email" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
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
                            <label class="text-xs font-semibold text-slate-500">Jenis kelamin</label>
                            <select name="jenis_kelamin" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="">Pilih jenis kelamin</option>
                                <option value="laki_laki">Laki-laki</option>
                                <option value="perempuan">Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">NIK</label>
                            <input name="nik" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">No HP</label>
                            <input name="no_hp" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Cabang</label>
                            <select name="cabang_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                @foreach ($cabangs as $cabang)
                                    <option value="">Pilih cabang</option>
                                    <option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Alamat</label>
                            <textarea name="alamat" required rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15"></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tempat Lahir</label>
                            <input name="tempat_lahir" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tanggal Lahir</label>
                            <input name="tanggal_lahir" type="date" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Asal Sekolah</label>
                            <input name="asal_sekolah" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">NIS (Bimbel)</label>
                            <input name="nis" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Materi Les</label>
                            <select name="materi_les_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="">Pilih materi</option>
                                @foreach ($materiLes as $materi)
                                    <option value="{{ $materi->id }}">{{ $materi->nama_materi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2 mt-4 pb-2 border-b"><h4 class="font-bold">Data Orang Tua</h4></div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Nama Ayah</label>
                            <input name="nama_ayah" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Pekerjaan Ayah</label>
                            <input name="pekerjaan_ayah" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tempat Lahir Ayah</label>
                            <input name="tempat_lahir_ayah" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tanggal Lahir Ayah</label>
                            <input name="tanggal_lahir_ayah" type="date" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Nama Ibu</label>
                            <input name="nama_ibu" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Pekerjaan Ibu</label>
                            <input name="pekerjaan_ibu" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tempat Lahir Ibu</label>
                            <input name="tempat_lahir_ibu" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tanggal Lahir Ibu</label>
                            <input name="tanggal_lahir_ibu" type="date" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">No HP Orang Tua</label>
                            <input name="no_hp_orang_tua" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Status</label>
                            <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="">Pilih status</option>
                                <option value="aktif">Aktif</option>
                                <option value="cuti">Cuti</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
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
                        <h3 class="text-lg font-bold tracking-tight text-slate-900">Edit siswa</h3>
                        <p class="mt-1 text-sm text-slate-500">Perubahan email akan mengikuti akun login siswa. Kosongkan kata sandi jika tidak diubah.</p>
                    </div>
                    <button type="button" @click="editOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" :action="`{{ url('/siswa') }}/${edit.id}`" class="mt-6 space-y-5">
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
                            <label class="text-xs font-semibold text-slate-500">Jenis kelamin</label>
                            <select name="jenis_kelamin" x-model="edit.jenis_kelamin" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="laki_laki">Laki-laki</option>
                                <option value="perempuan">Perempuan</option>
                            </select>
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
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Alamat</label>
                            <textarea name="alamat" x-model="edit.alamat" required rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15"></textarea>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tempat Lahir</label>
                            <input name="tempat_lahir" x-model="edit.tempat_lahir" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tanggal Lahir</label>
                            <input name="tanggal_lahir" type="date" x-model="edit.tanggal_lahir" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Asal Sekolah</label>
                            <input name="asal_sekolah" x-model="edit.asal_sekolah" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">NIS (Bimbel)</label>
                            <input name="nis" x-model="edit.nis" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Materi Les</label>
                            <select name="materi_les_id" x-model="edit.materi_les_id" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="">Pilih materi</option>
                                @foreach ($materiLes as $materi)
                                    <option value="{{ $materi->id }}">{{ $materi->nama_materi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2 mt-4 pb-2 border-b"><h4 class="font-bold">Data Orang Tua</h4></div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Nama Ayah</label>
                            <input name="nama_ayah" x-model="edit.nama_ayah" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Pekerjaan Ayah</label>
                            <input name="pekerjaan_ayah" x-model="edit.pekerjaan_ayah" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tempat Lahir Ayah</label>
                            <input name="tempat_lahir_ayah" x-model="edit.tempat_lahir_ayah" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tanggal Lahir Ayah</label>
                            <input name="tanggal_lahir_ayah" type="date" x-model="edit.tanggal_lahir_ayah" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Nama Ibu</label>
                            <input name="nama_ibu" x-model="edit.nama_ibu" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Pekerjaan Ibu</label>
                            <input name="pekerjaan_ibu" x-model="edit.pekerjaan_ibu" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tempat Lahir Ibu</label>
                            <input name="tempat_lahir_ibu" x-model="edit.tempat_lahir_ibu" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Tanggal Lahir Ibu</label>
                            <input name="tanggal_lahir_ibu" type="date" x-model="edit.tanggal_lahir_ibu" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">No HP Orang Tua</label>
                            <input name="no_hp_orang_tua" x-model="edit.no_hp_orang_tua" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Status</label>
                            <select name="status" x-model="edit.status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="aktif">Aktif</option>
                                <option value="cuti">Cuti</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
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
                <h3 class="text-lg font-bold text-slate-900">Hapus siswa</h3>
                <p class="mt-2 text-sm text-slate-600">Siswa dan akun login terkait akan dihapus.</p>
                <form method="POST" :action="`{{ url('/siswa') }}/${removeId}`" class="mt-6 flex flex-wrap justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">Hapus</button>
                </form>
            </div>
        </div>

        {{-- Modal: Cetak Kartu (Shared) --}}
        <template x-teleport="body">
            <div x-show="printOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" role="dialog">
                <div @click.outside="printOpen = false" class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-900/10">
                    <h3 class="text-lg font-bold text-slate-900 mb-4">Cetak Kartu Absensi</h3>
                    <p class="mb-4 text-sm text-slate-600" x-text="`Siswa: ${selectedSiswa.nama}`"></p>
                    <form action="{{ route('presensi.print-card') }}" method="GET" target="_blank" class="space-y-4">
                        <input type="hidden" name="student_id" :value="selectedSiswa.id">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Bulan</label>
                                <select name="bulan" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 outline-none">
                                    @foreach([1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'] as $num => $name)
                                        <option value="{{ $num }}" @selected($num == date('n'))>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Tahun</label>
                                <select name="tahun" required class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 outline-none">
                                    @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                                        <option value="{{ $y }}" @selected($y == date('Y'))>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="pt-4 flex justify-end gap-2 text-sm">
                            <button type="button" @click="printOpen = false" class="rounded-lg border border-slate-200 px-3 py-2 font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 font-semibold text-white hover:bg-emerald-700">Cetak Kartu</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-layouts.dashboard-shell>
