<x-layouts.dashboard-shell title="Siswa - Sistem Bimbel Jarimatrik">
    <div
        x-data="{
            createOpen: false,
            editOpen: false,
            editOpen: false,
            printOpen: false,
            selectedSiswa: { id: null, nama: '' },
            removeId: null,
            edit: {
                id: null,
                nama: '',
                jenis_kelamin: 'laki_laki',
                nik: '',
                no_hp: '',
                alamat: '',
                cabang_id: '',
                status: 'aktif',
                cuti_sampai: '',
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
                tanggal_daftar: '',
                registration_type: 'baru'
            },
            doEdit(item) {
                this.edit = {
                    id: item.id,
                    nama: item.nama,
                    jenis_kelamin: item.jenis_kelamin,
                    nik: item.nik || '',
                    no_hp: item.no_hp,
                    alamat: item.alamat,
                    cabang_id: item.cabang_id,
                    status: item.status,
                    cuti_sampai: item.cuti_sampai || '',
                    tempat_lahir: item.tempat_lahir || '',
                    tanggal_lahir: item.tanggal_lahir || '',
                    asal_sekolah: item.asal_sekolah || '',
                    nis: item.nis || '',
                    materi_les_id: item.materi_les_id || '',
                    nama_ayah: item.nama_ayah || '',
                    tempat_lahir_ayah: item.tempat_lahir_ayah || '',
                    tanggal_lahir_ayah: item.tanggal_lahir_ayah || '',
                    pekerjaan_ayah: item.pekerjaan_ayah || '',
                    nama_ibu: item.nama_ibu || '',
                    tempat_lahir_ibu: item.tempat_lahir_ibu || '',
                    tanggal_lahir_ibu: item.tanggal_lahir_ibu || '',
                    pekerjaan_ibu: item.pekerjaan_ibu || '',
                    no_hp_orang_tua: item.no_hp_orang_tua || '',
                    tanggal_daftar: item.created_at ? item.created_at.substring(0, 10) : '',
                    registration_type: item.registration_type || 'baru'
                };
                this.editOpen = true;
            },
            importOpen: false,
            formatIndoDate(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
            }
        }"
        class="space-y-6"
    >
        <x-module-page-header title="Data siswa" description="Kelola data siswa, pendaftaran, dan informasi terkait dalam satu sistem bimbel.">
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
                        <input name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Nama / NIK" class="mt-1.5 min-w-[220px] rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
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
                    <a href="{{ route('siswa.export.excel', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Unduh Data
                    </a>
                    <button @click="importOpen = true" type="button" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import Siswa
                    </button>
                    <button @click="createOpen = true" type="button" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 transition hover:bg-blue-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Siswa
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
                                <td class="px-4 py-3.5 font-medium text-slate-900">{{ $siswa->nama }} <p class="text-xs text-slate-500">{{ $siswa->created_at->format('d M Y') ?? '—' }}</p></td>
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
                                            @click="printOpen = true; selectedSiswa = { id: {{ $siswa->id }}, nama: @js($siswa->nama), created_at: '{{ $siswa->created_at }}' }"
                                            class="text-emerald-600 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 p-2 rounded-lg transition-colors"
                                            title="Kartu">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <rect x="2" y="5" width="20" height="14" rx="2" ry="2" stroke-width="2"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2 10h20"/>
                                            </svg>
                                        </button>

                                        <button
                                            type="button"
                                            @click="doEdit({{ json_encode($siswa) }})"
                                            class="text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 p-2 rounded-lg transition-colors"
                                            title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('siswa.destroy', $siswa) }}" class="inline" onsubmit="event.preventDefault(); confirmDelete(this, 'Hapus data siswa?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors" title="Hapus">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-slate-500">Belum ada data siswa.</td>
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
                    </div>
                    <button type="button" @click="createOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('siswa.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Jenis Siswa <span class="text-red-500">*</span></label>
                            <select name="registration_type" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="baru">Siswa Baru (Generate Tagihan Otomatis)</option>
                                <option value="lama">Siswa Lama (Tanpa Tagihan Otomatis)</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Nama <span class="text-red-500">*</span></label>
                            <input name="nama" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Jenis kelamin <span class="text-red-500">*</span></label>
                            <select name="jenis_kelamin" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
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
                            <label class="text-xs font-semibold text-slate-500">No HP <span class="text-red-500">*</span></label>
                            <input name="no_hp" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Cabang <span class="text-red-500">*</span></label>
                            <select name="cabang_id" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="">Pilih cabang</option>
                                @foreach ($cabangs as $cabang)
                                    <option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Alamat <span class="text-red-500">*</span></label>
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
                        <p class="mt-1 text-sm text-slate-500">Perbarui data profil dan informasi orang tua siswa.</p>
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
                            <label class="text-xs font-semibold text-slate-500">Jenis Siswa <span class="text-red-500">*</span></label>
                            <select name="registration_type" x-model="edit.registration_type" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="baru">Siswa Baru</option>
                                <option value="lama">Siswa Lama</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Nama</label>
                            <input name="nama" x-model="edit.nama" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Jenis kelamin</label>
                            <select name="jenis_kelamin" x-model="edit.jenis_kelamin" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="">Pilih Jenis Kelamin</option>
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
                                <option value="">Pilih Cabang</option>
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
                            <label class="text-xs font-semibold text-slate-500">Tanggal Daftar</label>
                            <input name="tanggal_daftar" type="date" x-model="edit.tanggal_daftar" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-slate-500">Status</label>
                            <select name="status" x-model="edit.status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="aktif">Aktif</option>
                                <option value="cuti">Cuti</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                        <div x-show="edit.status === 'cuti'" x-cloak x-transition class="sm:col-span-2">
                            <label class="text-xs font-semibold text-slate-500">Cuti Sampai Tanggal <span class="text-red-500">*</span></label>
                            <input name="cuti_sampai" type="date" x-model="edit.cuti_sampai" :required="edit.status === 'cuti'" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                        <button type="button" @click="editOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>


        {{-- Modal: Cetak Kartu (Shared) --}}
        <template x-teleport="body">
            <div x-show="printOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" role="dialog">
                <div @click.outside="printOpen = false" 
                     x-data="{
                        init() {
                            this.$watch('printOpen', (val) => {
                                if (val && selectedSiswa.created_at) {
                                    const regDate = new Date(selectedSiswa.created_at);
                                    const now = new Date();
                                    const day = String(regDate.getDate()).padStart(2, '0');
                                    const month = String(now.getMonth() + 1).padStart(2, '0');
                                    const year = now.getFullYear();
                                    
                                    this.$nextTick(() => {
                                        const startDateInput = document.getElementById('siswa-print-start-date');
                                        if (startDateInput) startDateInput.value = `${year}-${month}-${day}`;
                                    });
                                }
                            });
                        }
                     }"
                     class="w-full max-w-lg rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-900/10">
                    
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Cetak Kartu Absensi</h3>
                            <p class="text-sm text-slate-500 mt-1">
                                Siswa: <span class="font-semibold text-slate-900" x-text="selectedSiswa.nama"></span>
                                <span class="text-xs ml-1 opacity-70" x-text="` (Daftar: ${formatIndoDate(selectedSiswa.created_at)})`"></span>
                            </p>
                        </div>
                        <button @click="printOpen = false" class="h-8 w-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-100 hover:text-rose-600 transition-colors">
                            &times;
                        </button>
                    </div>

                    <form action="{{ route('presensi.print-card') }}" method="GET" target="_blank" class="space-y-6">
                        <input type="hidden" name="student_id" :value="selectedSiswa.id">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Periode Dari</label>
                                <input type="date" id="siswa-print-start-date" name="start_date" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm bg-white">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Periode Sampai</label>
                                <input type="date" name="end_date" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm bg-white">
                            </div>
                        </div>

                        <div class="pt-4 flex flex-col gap-3">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-4 text-sm font-bold text-white shadow-lg transition-all hover:bg-emerald-700 active:scale-[0.98]">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                Unduh Kartu Absensi
                            </button>
                            <button type="button" @click="printOpen = false" class="w-full py-2 text-sm font-semibold text-slate-500 hover:text-slate-800 transition">Batalkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- Modal: Import Siswa --}}
        <div x-show="importOpen" x-cloak x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm" role="dialog">
            <div @click.outside="importOpen = false" class="w-full max-w-xl rounded-3xl bg-white p-5 shadow-2xl ring-1 ring-slate-900/10">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">Import Data Siswa</h3>
                    </div>
                    <button @click="importOpen = false" class="h-8 w-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-100 hover:text-rose-600 transition-colors">
                        &times;
                    </button>
                </div>

                <div class="mb-2 rounded-2xl bg-blue-50 p-4 border border-blue-100">
                    <h4 class="text-sm font-bold text-blue-900 flex items-center gap-2 mb-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Petunjuk Penggunaan
                    </h4>
                    <ul class="text-xs text-blue-800 space-y-1 ml-6 list-disc">
                        <li>Gunakan file template yang telah disediakan untuk menghindari kesalahan.</li>
                        <li>Kolom <strong>Nama, Jenis_Kelamin, No_HP, Alamat, Cabang, Jenis Kelamin,</strong> dan <strong>Jenis_Siswa</strong> wajib diisi.</li>
                        <li>Nama Cabang dan Materi Les harus sesuai dengan yang ada di sistem.</li>
                    </ul>
                    <div class="mt-4">
                        <a href="{{ route('siswa.template') }}" class="inline-flex items-center gap-2 text-xs font-bold text-white hover:bg-emerald-600 bg-emerald-500 px-3 py-1.5 rounded-lg shadow-sm transition">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Download Template Excel
                        </a>
                    </div>
                </div>

                <form action="{{ route('siswa.import') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
                      x-data="{ 
                        importFile: null,
                        importFileInfo: '',
                        handleFile(e) {
                            const file = e.target.files[0];
                            if (file) {
                                this.importFile = file;
                                const size = (file.size / 1024).toFixed(1);
                                this.importFileInfo = `${file.name} (${size} KB)`;
                            } else {
                                this.importFile = null;
                                this.importFileInfo = '';
                            }
                        }
                      }">
                    @csrf
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pilih File Excel (.xlsx / .csv)</label>
                        <div class="relative group">
                            <input type="file" name="file" required @change="handleFile" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <div class="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-2xl py-2 px-2 bg-slate-50 group-hover:border-blue-400 group-hover:bg-blue-50 transition-all">
                                <svg class="h-10 w-10 text-slate-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <p class="mt-2 text-sm font-medium text-slate-600 group-hover:text-blue-700">Klik atau tarik file ke sini</p>
                                <p class="mt-1 text-xs text-slate-400">Maksimal 2MB</p>
                            </div>
                        </div>

                        {{-- Preview File --}}
                        <template x-if="importFile">
                            <div class="mt-3 flex items-center justify-between rounded-xl bg-emerald-50 border border-emerald-100 p-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 flex items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-emerald-900 truncate" x-text="importFileInfo"></p>
                                        <p class="text-[10px] text-emerald-600">File siap diimport</p>
                                    </div>
                                </div>
                                <button type="button" @click="importFile = null; $el.closest('form').reset()" class="text-xs font-bold text-rose-600 hover:text-rose-800 p-1">Hapus</button>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-between gap-8">
                        <button type="button" @click="importOpen = false" class="w-full py-2 text-sm font-semibold text-slate-500 hover:text-slate-800 transition text-center">Batalkan</button>
                        <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-3 py-2 text-sm font-bold text-white shadow-lg shadow-emerald-600/20 transition-all hover:bg-emerald-700 active:scale-[0.98]">
                            Mulai Import Data
                        </button>
                        
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard-shell>
