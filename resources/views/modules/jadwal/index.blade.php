@php
    $isSiswaJadwal = auth()->user()->hasRole('siswa');
    $isSuperAdminJadwal = auth()->user()->hasRole('super_admin');
    $isTutorJadwal = auth()->user()->hasRole('tutor');
    $canManageJadwal = auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']);
    $tutorsForAlpine = $tutors->map(fn ($t) => ['id' => (int) $t->id, 'nama' => $t->nama, 'cabang_id' => (int) $t->cabang_id])->values();
    $adminCabangId = $cabangs->first()?->id;
@endphp
<x-layouts.dashboard-shell title="Jadwal - eBimbel">
    <div
        x-data='{
            createOpen: false,
            editOpen: false,
            deleteOpen: false,
            tutorsAll: @json($tutorsForAlpine),
            createCabangId: "",
            createTutorId: "",
            edit: {
                id: null,
                tutor_id: "",
                cabang_id: "",
                mata_pelajaran_id: "",
                hari: "senin",
                jam_mulai: "08:00",
                jam_selesai: "09:00"
            },
            removeId: null,
            detailOpen: false,
            detail: {
                mapel: "",
                hari: "",
                jam: "",
                cabang: "",
                tutor: "",
                kode_mapel: "",
                peserta_count: 0,
                peserta: []
            },
            tutorsByCabang(cid) {
                if (!cid) return [];
                return this.tutorsAll.filter(t => String(t.cabang_id) === String(cid));
            },
            openCreate() {
                this.createOpen = true;
                this.createCabangId = "";
                this.createTutorId = "";
            },
            onCreateCabangChange() {
                this.createTutorId = "";
            },
            onEditCabangChange() {
                const list = this.tutorsByCabang(this.edit.cabang_id);
                if (!list.some(t => String(t.id) === String(this.edit.tutor_id))) {
                    this.edit.tutor_id = "";
                }
            }
        }'
    >
        <x-module-page-header
            title="{{ $isSiswaJadwal ? 'Jadwal sesi saya' : 'Jadwal dan sesi kelas' }}"
            :description="$isSiswaJadwal ? 'Sesi kelas yang terhubung dengan akun Anda (berdasarkan riwayat kehadiran).' : 'Kalender mingguan, tutor, cabang, dan daftar sesi.'"
        >
        </x-module-page-header>

        <form method="GET" class="mb-6 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
            <div class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cabang</label>
                        <select name="cabang_id" class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            <option value="">Semua cabang</option>
                            @foreach ($cabangs as $cabang)
                                <option value="{{ $cabang->id }}" @selected(($filters['cabang_id'] ?? null) == $cabang->id)>{{ $cabang->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Hari</label>
                    <select name="hari" class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Semua hari</option>
                        @foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'] as $hari)
                            <option value="{{ $hari }}" @selected(($filters['hari'] ?? '') === $hari)>{{ ucfirst($hari) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Terapkan</button>
                <a href="{{ route('jadwal.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
                @if ($canManageJadwal)
                    <button @click="openCreate()" type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                        Buat sesi baru
                    </button>
                @endif
            </div>
        </form>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="grid grid-cols-7 divide-x divide-slate-100 border-b border-slate-200 bg-slate-50 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $d)
                    <div class="px-2 py-3">{{ $d }}</div>
                @endforeach
            </div>
            <div class="grid min-h-[220px] grid-cols-7 divide-x divide-slate-100 text-sm">
                @foreach (range(0, 6) as $i)
                    <div class="space-y-2 p-2 align-top">
                        @foreach ($jadwals->where('hari', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'][$i])->take(2) as $item)
                            <div class="rounded-lg border border-blue-100 bg-blue-50/80 p-2">
                                <p class="font-semibold text-slate-900">{{ substr($item->jam_mulai, 0, 5) }} {{ $item->mapel }}</p>
                                <p class="text-xs text-slate-600">{{ optional($item->cabang)->nama_cabang }} - {{ optional($item->tutor)->nama }}</p>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Daftar sesi</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-left text-xs font-semibold uppercase text-slate-500">
                        <tr>
                            <th class="py-2 pr-4">Hari</th>
                            <th class="py-2 pr-4">Waktu</th>
                            <th class="py-2 pr-4">Mapel</th>
                            <th class="py-2 pr-4">Tutor</th>
                            <th class="py-2 pr-4">Cabang</th>
                            @if (! $isSiswaJadwal)
                                <th class="py-2 pr-4">Peserta</th>
                                <th class="py-2">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($jadwals as $j)
                            <tr>
                                <td class="py-3 pr-4">{{ ucfirst($j->hari) }}</td>
                                <td class="py-3 pr-4">{{ substr($j->jam_mulai, 0, 5) }}-{{ substr($j->jam_selesai, 0, 5) }}</td>
                                <td class="py-3 pr-4">{{ $j->mapel }}</td>
                                <td class="py-3 pr-4">{{ optional($j->tutor)->nama }}</td>
                                <td class="py-3 pr-4">{{ optional($j->cabang)->nama_cabang }}</td>
                                @if (! $isSiswaJadwal)
                                    <td class="py-3 pr-4">
                                        <span class="font-medium text-slate-800">{{ $j->siswas_count ?? 0 }}</span>
                                        @if ($canManageJadwal)
                                            <a href="{{ route('jadwal.peserta', $j) }}" class="ml-2 text-sm font-semibold text-blue-600 hover:text-blue-800">Kelola</a>
                                        @endif
                                    </td>
                                    <td class="py-3 space-x-3">
                                        @if ($canManageJadwal)
                                            <button @click="editOpen = true; edit = {id: {{ $j->id }}, tutor_id: '{{ $j->tutor_id }}', cabang_id: '{{ $j->cabang_id }}', mata_pelajaran_id: '{{ $j->mata_pelajaran_id }}', hari: @js($j->hari), jam_mulai: @js(substr($j->jam_mulai, 0, 5)), jam_selesai: @js(substr($j->jam_selesai, 0, 5))}" type="button" class="text-blue-600 hover:underline">Edit</button>
                                            <button @click="deleteOpen = true; removeId = {{ $j->id }}" type="button" class="text-rose-600 hover:underline">Delete</button>
                                        @elseif ($isTutorJadwal)
                                            <button
                                                type="button"
                                                data-jadwal-detail="{{ json_encode([
                                                    'mapel' => $j->mapel,
                                                    'hari' => ucfirst($j->hari),
                                                    'jam' => substr($j->jam_mulai, 0, 5).' – '.substr($j->jam_selesai, 0, 5),
                                                    'cabang' => optional($j->cabang)->nama_cabang ?? '—',
                                                    'tutor' => optional($j->tutor)->nama ?? '—',
                                                    'kode_mapel' => optional($j->mataPelajaran)->kode ?? '',
                                                    'peserta_count' => (int) ($j->siswas_count ?? 0),
                                                    'peserta' => $j->relationLoaded('siswas') ? $j->siswas->pluck('nama')->values()->all() : [],
                                                ]) }}"
                                                @click="detailOpen = true; detail = JSON.parse($event.currentTarget.dataset.jadwalDetail)"
                                                class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline"
                                            >Lihat</button>
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr><td colspan="{{ $isSiswaJadwal ? 5 : 7 }}" class="py-6 text-center text-slate-500">Belum ada sesi jadwal.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $jadwals->links() }}</div>
        </section>

        @if ($isTutorJadwal)
            <div
                x-show="detailOpen"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]"
                role="dialog"
                aria-modal="true"
                aria-labelledby="jadwal-detail-title"
            >
                <div
                    @click.outside="detailOpen = false"
                    @keydown.escape.window="detailOpen = false"
                    class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white shadow-xl ring-1 ring-slate-900/5"
                >
                    <div class="border-b border-slate-100 bg-gradient-to-br from-blue-600 to-blue-700 px-6 py-5 text-white">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/20">
                                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5a2.25 2.25 0 002.25-2.25m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5a2.25 2.25 0 012.25 2.25v7.5"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h3 id="jadwal-detail-title" class="text-lg font-bold tracking-tight" x-text="detail.mapel">Sesi</h3>
                                    <p class="mt-0.5 text-sm text-blue-100" x-text="detail.hari + ' · ' + detail.jam"></p>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="detailOpen = false"
                                class="shrink-0 rounded-lg p-1.5 text-white/90 hover:bg-white/10"
                                aria-label="Tutup"
                            >
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="space-y-6 px-6 py-5">
                        <dl class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cabang</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900" x-text="detail.cabang"></dd>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tutor</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900" x-text="detail.tutor"></dd>
                            </div>
                            <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 sm:col-span-2">
                                <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Mata pelajaran</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-900">
                                    <span x-text="detail.mapel"></span>
                                    <span
                                        x-show="detail.kode_mapel"
                                        class="ml-2 inline-flex items-center rounded-md bg-slate-200/80 px-2 py-0.5 text-xs font-semibold text-slate-700"
                                        x-text="detail.kode_mapel ? '(' + detail.kode_mapel + ')' : ''"
                                    ></span>
                                </dd>
                            </div>
                        </dl>
                        <div>
                            <div class="flex items-center justify-between gap-2 border-b border-slate-100 pb-2">
                                <h4 class="text-sm font-semibold text-slate-900">Peserta terdaftar</h4>
                                <span class="rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700">
                                    <span x-text="detail.peserta_count"></span> siswa
                                </span>
                            </div>
                            <ul
                                x-show="detail.peserta && detail.peserta.length"
                                class="mt-3 max-h-48 list-none space-y-1.5 overflow-y-auto rounded-xl border border-slate-100 bg-slate-50/50 p-3 text-sm text-slate-800"
                            >
                                <template x-for="(nama, i) in detail.peserta" :key="i">
                                    <li class="flex items-center gap-2 rounded-lg bg-white px-3 py-2 shadow-sm ring-1 ring-slate-100">
                                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-xs font-bold text-blue-800" x-text="i + 1"></span>
                                        <span x-text="nama" class="min-w-0 truncate font-medium"></span>
                                    </li>
                                </template>
                            </ul>
                            <p
                                x-show="!detail.peserta || !detail.peserta.length"
                                class="mt-3 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-900"
                            >
                                Belum ada siswa terdaftar di kelas ini. Admin dapat menambahkan peserta lewat menu <strong>Jadwal</strong> → <strong>Kelola</strong>.
                            </p>
                        </div>
                        <div class="flex justify-end border-t border-slate-100 pt-4">
                            <button
                                type="button"
                                @click="detailOpen = false"
                                class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800"
                            >Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($canManageJadwal)
            <div x-show="createOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true">
                <div @click.outside="createOpen = false" @keydown.escape.window="createOpen = false" class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold tracking-tight text-slate-900">Buat sesi baru</h3>
                            <p class="mt-1 text-sm text-slate-500">Pilih cabang dulu, lalu tutor yang bertugas di cabang tersebut.</p>
                        </div>
                        <button type="button" @click="createOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('jadwal.store') }}" class="mt-6 space-y-4">
                        @csrf
                        @if ($isSuperAdminJadwal)
                            <div>
                                <label for="jadwal-create-cabang" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cabang</label>
                                <select id="jadwal-create-cabang" name="cabang_id" x-model="createCabangId" @change="onCreateCabangChange()" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                    <option value="">Pilih cabang</option>
                                    @foreach ($cabangs as $cabang)
                                        <option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="jadwal-create-tutor" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Tutor</label>
                                <select id="jadwal-create-tutor" name="tutor_id" x-model="createTutorId" :disabled="!createCabangId" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400">
                                    <option value="">Pilih tutor</option>
                                    <template x-for="t in tutorsByCabang(createCabangId)" :key="t.id">
                                        <option :value="t.id" x-text="t.nama"></option>
                                    </template>
                                </select>
                                <p class="mt-1.5 text-xs text-slate-500" x-show="createCabangId && tutorsByCabang(createCabangId).length === 0">Belum ada tutor di cabang ini. Tambahkan tutor di menu Tutor.</p>
                            </div>
                        @else
                            <input type="hidden" name="cabang_id" value="{{ $adminCabangId }}">
                            <div>
                                <label for="jadwal-create-tutor-ac" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Tutor</label>
                                <select id="jadwal-create-tutor-ac" name="tutor_id" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                    <option value="">Pilih tutor</option>
                                    @foreach ($tutors as $tutor)
                                        <option value="{{ $tutor->id }}">{{ $tutor->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label for="jadwal-create-mapel" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Mata pelajaran</label>
                            <select id="jadwal-create-mapel" name="mata_pelajaran_id" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="">Pilih mata pelajaran</option>
                                @foreach ($mataPelajarans as $mp)
                                    <option value="{{ $mp->id }}">{{ $mp->nama }}@if ($mp->kode) ({{ $mp->kode }})@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="jadwal-create-hari" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Hari</label>
                            <select id="jadwal-create-hari" name="hari" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                @foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'] as $h)
                                    <option value="{{ $h }}">{{ ucfirst($h) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="jadwal-create-mulai" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Jam mulai</label>
                                <input id="jadwal-create-mulai" name="jam_mulai" type="time" required value="08:00" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label for="jadwal-create-selesai" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Jam selesai</label>
                                <input id="jadwal-create-selesai" name="jam_selesai" type="time" required value="09:00" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                        </div>
                        <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                            <button type="button" @click="createOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            <div x-show="editOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true">
                <div @click.outside="editOpen = false" @keydown.escape.window="editOpen = false" class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-bold tracking-tight text-slate-900">Edit sesi</h3>
                            <p class="mt-1 text-sm text-slate-500">Ubah cabang, tutor, mapel, atau waktu sesi.</p>
                        </div>
                        <button type="button" @click="editOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" :action="`{{ url('/jadwal') }}/${edit.id}`" class="mt-6 space-y-4">
                        @csrf
                        @method('PUT')
                        @if ($isSuperAdminJadwal)
                            <div>
                                <label for="jadwal-edit-cabang" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cabang</label>
                                <select id="jadwal-edit-cabang" name="cabang_id" x-model="edit.cabang_id" @change="onEditCabangChange()" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                    @foreach ($cabangs as $cabang)
                                        <option value="{{ $cabang->id }}">{{ $cabang->nama_cabang }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="jadwal-edit-tutor" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Tutor</label>
                                <select id="jadwal-edit-tutor" name="tutor_id" x-model="edit.tutor_id" :disabled="!edit.cabang_id" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15 disabled:cursor-not-allowed disabled:bg-slate-50">
                                    <option value="">Pilih tutor</option>
                                    <template x-for="t in tutorsByCabang(edit.cabang_id)" :key="t.id">
                                        <option :value="t.id" x-text="t.nama"></option>
                                    </template>
                                </select>
                            </div>
                        @else
                            <input type="hidden" name="cabang_id" :value="edit.cabang_id">
                            <div>
                                <label for="jadwal-edit-tutor-ac" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Tutor</label>
                                <select id="jadwal-edit-tutor-ac" name="tutor_id" x-model="edit.tutor_id" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                    @foreach ($tutors as $tutor)
                                        <option value="{{ $tutor->id }}">{{ $tutor->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label for="jadwal-edit-mapel" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Mata pelajaran</label>
                            <select id="jadwal-edit-mapel" name="mata_pelajaran_id" x-model="edit.mata_pelajaran_id" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                @foreach ($mataPelajarans as $mp)
                                    <option value="{{ $mp->id }}">{{ $mp->nama }}@if ($mp->kode) ({{ $mp->kode }})@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="jadwal-edit-hari" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Hari</label>
                            <select id="jadwal-edit-hari" name="hari" x-model="edit.hari" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                @foreach (['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'] as $h)
                                    <option value="{{ $h }}">{{ ucfirst($h) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="jadwal-edit-mulai" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Jam mulai</label>
                                <input id="jadwal-edit-mulai" name="jam_mulai" type="time" x-model="edit.jam_mulai" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label for="jadwal-edit-selesai" class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Jam selesai</label>
                                <input id="jadwal-edit-selesai" name="jam_selesai" type="time" x-model="edit.jam_selesai" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                        </div>
                        <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                            <button type="button" @click="editOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Perbarui</button>
                        </div>
                    </form>
                </div>
            </div>

            <div x-show="deleteOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true">
                <div @click.outside="deleteOpen = false" @keydown.escape.window="deleteOpen = false" class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                    <h3 class="text-lg font-bold text-slate-900">Hapus sesi</h3>
                    <p class="mt-2 text-sm text-slate-600">Sesi jadwal akan dihapus. Tindakan ini tidak dapat dibatalkan.</p>
                    <form method="POST" :action="`{{ url('/jadwal') }}/${removeId}`" class="mt-6 flex flex-wrap justify-end gap-2">
                        @csrf
                        @method('DELETE')
                        <button type="button" @click="deleteOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">Hapus</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts.dashboard-shell>
