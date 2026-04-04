@php
    $isSiswa = auth()->user()->hasRole('siswa');
    $isTutor = auth()->user()->hasRole('tutor');
    $isStaffRekap = auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']);
    $presensiDesc = $isSiswa
        ? 'Riwayat kehadiran Anda per kelas yang diikuti.'
        : ($isTutor
            ? 'Isi presensi per kelas dan tanggal. Siswa yang tampil hanya yang terdaftar di kelas (menu Jadwal → Kelola peserta).'
            : 'Rekap kehadiran per kelas; filter tanggal, kelas, dan status.');
@endphp
<x-layouts.dashboard-shell title="Presensi — eBimbel">
    <x-module-page-header title="Presensi & kehadiran" :description="$presensiDesc">
        <x-slot name="actions">
            @if ($isStaffRekap)
                <a href="{{ route('presensi.export', request()->query()) }}" class="inline-flex rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Ekspor rekap
                </a>
            @endif
        </x-slot>
    </x-module-page-header>

    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    @if ($errors->any() && ($errors->has('statuses') || $errors->has('jadwal_id')))
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($isTutor)
        @php
            $bukaModalSekali = (bool) session()->pull('open_presensi_modal', false);
        @endphp
        <div x-data="{ presensiModalOpen: {{ $bukaModalSekali ? 'true' : 'false' }} }" class="mb-6">
            <section class="rounded-2xl border border-blue-100 bg-gradient-to-br from-blue-700 to-blue-900 p-6 shadow-sm">
                <h2 class="text-sm font-semibold text-white">Pilih kelas & tanggal</h2>
                <p class="mt-1 text-xs text-slate-600 text-white">Klik <strong>Muat peserta</strong> untuk memuat daftar, lalu <strong>Buka form presensi</strong> untuk mengisi atau memperbarui kehadiran di popup.</p>
                <form method="GET" action="{{ route('presensi.index') }}" class="mt-4 flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-white">Kelas / jadwal</label>
                        <select name="kelas_jadwal_id" class="mt-1 min-w-[220px] rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                            <option value="">Pilih kelas</option>
                            @foreach ($tutor_jadwal_choices as $jd)
                                <option value="{{ $jd->id }}" @selected(($filters['kelas_jadwal_id'] ?? '') == (string) $jd->id)>
                                    {{ $jd->mapel }} — {{ ucfirst($jd->hari) }} {{ substr($jd->jam_mulai, 0, 5) }} ({{ optional($jd->cabang)->nama_cabang }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 text-white">Tanggal sesi</label>
                        <input type="date" name="kelas_tanggal" value="{{ $filters['kelas_tanggal'] ?? now()->format('Y-m-d') }}" class="mt-1 rounded-xl border border-slate-200 px-3 py-2.5 text-sm">
                    </div>
                    <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Muat peserta</button>
                    @if ($kelas_context && ! $kelas_context['peserta_kosong'])
                        <button type="button" @click="presensiModalOpen = true" class="rounded-xl border border-blue-300 bg-white px-4 py-2.5 text-sm font-semibold text-blue-800 shadow-sm hover:bg-blue-50">
                            Buka form presensi
                        </button>
                    @endif
                </form>

                @if ($kelas_context && $kelas_context['peserta_kosong'])
                    <p class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Belum ada siswa terdaftar di kelas ini. Admin cabang/super admin dapat menambah peserta lewat menu <strong>Jadwal</strong> → <strong>Kelola</strong>.</p>
                @endif
            </section>

            @if ($kelas_context && ! $kelas_context['peserta_kosong'])
                <div
                    x-show="presensiModalOpen"
                    x-cloak
                    x-transition.opacity
                    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]"
                    role="dialog"
                    aria-modal="true"
                >
                    <div
                        @click.outside="presensiModalOpen = false"
                        @keydown.escape.window="presensiModalOpen = false"
                        class="max-h-[90vh] w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl ring-1 ring-slate-900/5"
                    >
                        <div class="flex items-start justify-between gap-4 border-b border-slate-100 bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 text-white">
                            <div>
                                <h3 class="text-lg font-bold tracking-tight">
                                    {{ $kelas_context['ada_data_presensi'] ? 'Ubah kehadiran' : 'Input presensi' }}
                                </h3>
                                <p class="mt-1 text-sm text-blue-100">
                                    {{ $kelas_context['jadwal']->mapel }} · {{ ucfirst($kelas_context['jadwal']->hari) }}
                                    · {{ $kelas_context['tanggal']->translatedFormat('d M Y') }}
                                </p>
                                @if ($kelas_context['ada_data_presensi'])
                                    <p class="mt-2 text-xs font-medium text-blue-50/95">Data presensi untuk tanggal ini sudah ada — simpan untuk memperbarui status.</p>
                                @endif
                            </div>
                            <button type="button" @click="presensiModalOpen = false" class="shrink-0 rounded-lg p-1.5 text-white/90 hover:bg-white/10" aria-label="Tutup">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <form method="POST" action="{{ route('presensi.store-sesi') }}" class="flex max-h-[calc(90vh-5.5rem)] flex-col">
                            @csrf
                            <input type="hidden" name="jadwal_id" value="{{ $kelas_context['jadwal']->id }}">
                            <input type="hidden" name="tanggal" value="{{ $kelas_context['tanggal']->format('Y-m-d') }}">
                            <div class="overflow-y-auto px-6 py-4">
                                <table class="min-w-full divide-y divide-slate-200 text-sm">
                                    <thead class="sticky top-0 bg-white text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        <tr>
                                            <th class="py-3 pr-4">Nama siswa</th>
                                            <th class="py-3">Kehadiran</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($kelas_context['rows'] as $row)
                                            @php $sid = $row['siswa']->id; $cur = $row['status_saat_ini']; @endphp
                                            <tr>
                                                <td class="py-3 pr-4 font-medium text-slate-900">{{ $row['siswa']->nama }}</td>
                                                <td class="py-3">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-2 border-blue-200 bg-blue-50/90 px-3 py-2 text-sm font-bold text-blue-900 shadow-sm transition hover:border-blue-400 has-[:checked]:border-blue-600 has-[:checked]:ring-2 has-[:checked]:ring-blue-500/40">
                                                            <input type="radio" name="statuses[{{ $sid }}]" value="hadir" class="h-4 w-4 border-blue-400 text-blue-600 focus:ring-blue-500" @checked($cur === 'hadir') required>
                                                            <span>H</span>
                                                        </label>
                                                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-2 border-blue-200 bg-blue-50/90 px-3 py-2 text-sm font-bold text-blue-900 shadow-sm transition hover:border-blue-400 has-[:checked]:border-blue-600 has-[:checked]:ring-2 has-[:checked]:ring-blue-500/40">
                                                            <input type="radio" name="statuses[{{ $sid }}]" value="izin" class="h-4 w-4 border-blue-400 text-blue-600 focus:ring-blue-500" @checked($cur === 'izin')>
                                                            <span>I</span>
                                                        </label>
                                                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-2 border-blue-200 bg-blue-50/90 px-3 py-2 text-sm font-bold text-blue-900 shadow-sm transition hover:border-blue-400 has-[:checked]:border-blue-600 has-[:checked]:ring-2 has-[:checked]:ring-blue-500/40">
                                                            <input type="radio" name="statuses[{{ $sid }}]" value="sakit" class="h-4 w-4 border-blue-400 text-blue-600 focus:ring-blue-500" @checked($cur === 'sakit')>
                                                            <span>S</span>
                                                        </label>
                                                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-2 border-rose-200 bg-rose-50/90 px-3 py-2 text-sm font-bold text-rose-900 shadow-sm transition hover:border-rose-400 has-[:checked]:border-rose-600 has-[:checked]:ring-2 has-[:checked]:ring-rose-500/40">
                                                            <input type="radio" name="statuses[{{ $sid }}]" value="alfa" class="h-4 w-4 border-rose-400 text-rose-600 focus:ring-rose-500" @checked($cur === 'alfa')>
                                                            <span>A</span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 bg-slate-50/80 px-6 py-4">
                                <button type="button" @click="presensiModalOpen = false" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                                <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                                    {{ $kelas_context['ada_data_presensi'] ? 'Perbarui presensi' : 'Simpan presensi' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($isSiswa)
        <section class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('presensi.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="min-w-[min(100%,280px)] flex-1">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Mata pelajaran / kelas</label>
                    <select name="jadwal_id" class="mt-1 w-full min-w-[220px] rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">— Semua kelas —</option>
                        @foreach ($presensi_jadwals as $jd)
                            <option value="{{ $jd->id }}" @selected(($filters['jadwal_id'] ?? '') == (string) $jd->id)>
                                {{ $jd->mapel }} — {{ ucfirst($jd->hari) }}, {{ substr($jd->jam_mulai, 0, 5) }}-{{ substr($jd->jam_selesai, 0, 5) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800">Tampilkan</button>
                <a href="{{ route('presensi.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
            </form>
            @if ($presensi_jadwals->isEmpty())
                <p class="mt-3 text-sm text-amber-800">Anda belum terdaftar di kelas manapun. Hubungi admin jika seharusnya sudah enroll.</p>
            @endif
        </section>
    @elseif (! $isTutor && $isStaffRekap)
        <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal</label>
                <input name="tanggal" type="date" value="{{ $filters['tanggal'] ?? '' }}" class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm">
            </div>
            @if ($presensi_jadwal_rekap->isNotEmpty())
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Kelas / jadwal</label>
                    <select name="jadwal_id" class="mt-1 min-w-[220px] rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Semua kelas</option>
                        @foreach ($presensi_jadwal_rekap as $jd)
                            <option value="{{ $jd->id }}" @selected(($filters['jadwal_id'] ?? '') == (string) $jd->id)>
                                {{ $jd->mapel }} — {{ optional($jd->cabang)->nama_cabang }} ({{ ucfirst($jd->hari) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" class="mt-1 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    <option value="hadir" @selected(($filters['status'] ?? '') === 'hadir')>Hadir</option>
                    <option value="izin" @selected(($filters['status'] ?? '') === 'izin')>Izin</option>
                    <option value="sakit" @selected(($filters['status'] ?? '') === 'sakit')>Sakit</option>
                    <option value="alfa" @selected(($filters['status'] ?? '') === 'alfa')>Alfa</option>
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Terapkan</button>
            <a href="{{ route('presensi.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
        </form>
    @endif

    @if (! empty($summary['siswa_mode']))
        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-xl border border-slate-100 bg-gradient-to-br from-blue-50 to-white p-4 text-blue-950 shadow-sm ring-1 ring-blue-100">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700/80">Indeks kehadiran</p>
                <p class="mt-1 text-2xl font-bold">{{ $summary['pct_bulan'] }}%</p>
                <p class="mt-1 text-xs text-blue-800/90">Bulan berjalan ({{ $summary['hadir_bulan_ini'] ?? 0 }}/{{ $summary['total_bulan_ini'] ?? 0 }} sesi)</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sesi pada filter</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $summary['sesi_tercatat'] ?? 0 }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Presensi terakhir</p>
                <p class="mt-1 text-xl font-bold text-slate-900">{{ $summary['terakhir_label'] ?? '—' }}</p>
            </div>
        </div>
    @endif


    @if ($isSiswa)
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-300 text-sm">
                    <thead>
                        <tr class="bg-gradient-to-b from-slate-100 to-slate-200 text-xs font-bold uppercase tracking-wide text-slate-800">
                            <th rowspan="2" class="border border-slate-300 px-2 py-2 text-center align-middle">No</th>
                            <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left align-middle">Tanggal</th>
                            <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left align-middle">Mata pelajaran</th>
                            <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left align-middle">Waktu</th>
                            <th colspan="4" class="border border-slate-300 px-3 py-2 text-center">Presensi pertemuan</th>
                        </tr>
                        <tr class="bg-slate-100/95 text-[11px] font-bold uppercase tracking-wide text-slate-700">
                            <th class="border border-slate-300 px-2 py-2 text-center text-emerald-900">Hadir</th>
                            <th class="border border-slate-300 px-2 py-2 text-center text-blue-900">Izin</th>
                            <th class="border border-slate-300 px-2 py-2 text-center text-amber-900">Sakit</th>
                            <th class="border border-slate-300 px-2 py-2 text-center text-rose-900">Alpa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($presensis as $p)
                            @php
                                $st = $p->status;
                                $j = $p->jadwal;
                                $waktu = $j
                                    ? ucfirst($j->hari).', '.substr($j->jam_mulai, 0, 5).'–'.substr($j->jam_selesai, 0, 5)
                                    : '—';
                                $no = ($presensis->firstItem() ?? 1) + $loop->index;
                            @endphp
                            <tr class="text-slate-800 odd:bg-white even:bg-slate-50/80">
                                <td class="border border-slate-300 px-2 py-2 text-center font-medium">{{ $no }}</td>
                                <td class="border border-slate-300 px-3 py-2 font-mono text-xs">{{ optional($p->tanggal)->format('d/m/Y') }}</td>
                                <td class="border border-slate-300 px-3 py-2 font-medium">{{ optional($j)->mapel ?? '—' }}</td>
                                <td class="border border-slate-300 px-3 py-2 text-slate-700">{{ $waktu }}</td>
                                <td class="border border-slate-300 px-2 py-2 text-center">
                                    <input type="radio" disabled class="h-4 w-4 border-slate-400 text-emerald-600" name="kehadiran-lihat-{{ $p->id }}" @checked($st === 'hadir') aria-label="Hadir" title="Hadir">
                                </td>
                                <td class="border border-slate-300 px-2 py-2 text-center">
                                    <input type="radio" disabled class="h-4 w-4 border-slate-400 text-blue-600" name="kehadiran-lihat-{{ $p->id }}" @checked($st === 'izin') aria-label="Izin" title="Izin">
                                </td>
                                <td class="border border-slate-300 px-2 py-2 text-center">
                                    <input type="radio" disabled class="h-4 w-4 border-slate-400 text-amber-600" name="kehadiran-lihat-{{ $p->id }}" @checked($st === 'sakit') aria-label="Sakit" title="Sakit">
                                </td>
                                <td class="border border-slate-300 px-2 py-2 text-center">
                                    <input type="radio" disabled class="h-4 w-4 border-slate-400 text-rose-600" name="kehadiran-lihat-{{ $p->id }}" @checked($st === 'alfa') aria-label="Alpa" title="Alpa">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="border border-slate-300 px-4 py-8 text-center text-slate-500">Belum ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-2 border-t border-slate-200 bg-slate-50/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm font-medium text-slate-700">Total: {{ $presensis->total() }}</p>
                <div>{{ $presensis->links() }}</div>
            </div>
        </div>
        <p class="mt-2 text-xs text-slate-500">Kolom presensi hanya menampilkan status yang dicatat tutor (radio tidak dapat diubah).</p>
    @elseif ($isTutor)
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-slate-300 text-sm">
                    <thead>
                        <tr class="bg-gradient-to-b from-slate-100 to-slate-200 text-xs font-bold uppercase tracking-wide text-slate-800">
                            <th rowspan="2" class="border border-slate-300 px-2 py-2 text-center align-middle">No</th>
                            <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left align-middle">Tanggal</th>
                            <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left align-middle">Kelas</th>
                            <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left align-middle">Siswa</th>
                            <th colspan="4" class="border border-slate-300 px-3 py-2 text-center">Presensi pertemuan</th>
                            <th rowspan="2" class="border border-slate-300 px-3 py-2 text-left align-middle">Dicatat oleh</th>
                        </tr>
                        <tr class="bg-slate-100/95 text-[11px] font-bold uppercase tracking-wide text-slate-700">
                            <th class="border border-slate-300 px-2 py-2 text-center text-emerald-900">Hadir</th>
                            <th class="border border-slate-300 px-2 py-2 text-center text-blue-900">Izin</th>
                            <th class="border border-slate-300 px-2 py-2 text-center text-amber-900">Sakit</th>
                            <th class="border border-slate-300 px-2 py-2 text-center text-rose-900">Alpa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($presensis as $p)
                            @php
                                $st = $p->status;
                                $no = ($presensis->firstItem() ?? 1) + $loop->index;
                            @endphp
                            <tr class="text-slate-800 odd:bg-white even:bg-slate-50/80">
                                <td class="border border-slate-300 px-2 py-2 text-center font-medium">{{ $no }}</td>
                                <td class="border border-slate-300 px-3 py-2 font-mono text-xs">{{ optional($p->tanggal)->format('d/m/Y') }}</td>
                                <td class="border border-slate-300 px-3 py-2 font-medium">{{ optional($p->jadwal)->mapel ?? '—' }}</td>
                                <td class="border border-slate-300 px-3 py-2">{{ optional($p->siswa)->nama ?? '—' }}</td>
                                <td class="border border-slate-300 px-2 py-2 text-center">
                                    <input type="radio" disabled class="h-4 w-4 border-slate-400 text-emerald-600" name="tutor-rekap-{{ $p->id }}" @checked($st === 'hadir') aria-label="Hadir" title="Hadir">
                                </td>
                                <td class="border border-slate-300 px-2 py-2 text-center">
                                    <input type="radio" disabled class="h-4 w-4 border-slate-400 text-blue-600" name="tutor-rekap-{{ $p->id }}" @checked($st === 'izin') aria-label="Izin" title="Izin">
                                </td>
                                <td class="border border-slate-300 px-2 py-2 text-center">
                                    <input type="radio" disabled class="h-4 w-4 border-slate-400 text-amber-600" name="tutor-rekap-{{ $p->id }}" @checked($st === 'sakit') aria-label="Sakit" title="Sakit">
                                </td>
                                <td class="border border-slate-300 px-2 py-2 text-center">
                                    <input type="radio" disabled class="h-4 w-4 border-slate-400 text-rose-600" name="tutor-rekap-{{ $p->id }}" @checked($st === 'alfa') aria-label="Alpa" title="Alpa">
                                </td>
                                <td class="border border-slate-300 px-3 py-2 text-slate-600">{{ optional($p->creator)->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="border border-slate-300 px-4 py-8 text-center text-slate-500">Belum ada data presensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-2 border-t border-slate-200 bg-slate-50/50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm font-medium text-slate-700">Total: {{ $presensis->total() }}</p>
                <div>{{ $presensis->links() }}</div>
            </div>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Kelas</th>
                            <th class="px-4 py-3">Tutor</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Dicatat oleh</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($presensis as $p)
                            @php $st = $p->status; @endphp
                            <tr class="text-slate-700">
                                <td class="px-4 py-3 font-mono text-xs">{{ optional($p->tanggal)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ optional($p->jadwal)->mapel }}</td>
                                <td class="px-4 py-3">{{ optional($p->tutor)->nama ?? optional(optional($p->jadwal)->tutor)->nama ?? '—' }}</td>
                                <td class="px-4 py-3 font-medium">{{ optional($p->siswa)->nama }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold @class([
                                        'bg-emerald-100 text-emerald-800' => $st === 'hadir',
                                        'bg-rose-100 text-rose-800' => $st === 'alfa',
                                        'bg-blue-100 text-blue-800' => $st === 'izin',
                                        'bg-yellow-100 text-yellow-800' => $st === 'sakit',
                                    ])">{{ ucfirst($st) }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-600">{{ optional($p->creator)->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada data presensi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3">{{ $presensis->links() }}</div>
        </div>
    @endif
</x-layouts.dashboard-shell>
