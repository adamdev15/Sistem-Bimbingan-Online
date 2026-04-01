@php
    $isSiswa = auth()->user()->hasRole('siswa');
    $presensiDesc = $isSiswa
        ? 'Riwayat kehadiran Anda per sesi; sesuaikan filter untuk melihat periode atau mapel tertentu.'
        : (auth()->user()->hasRole('tutor')
            ? 'Rekap kehadiran siswa untuk sesi yang Anda ajar.'
            : 'Rekap per sesi untuk siswa dan konfirmasi kehadiran tutor. Operator dapat memverifikasi; siswa/tutor melihat riwayat.');
@endphp
<x-layouts.dashboard-shell title="Presensi — eBimbel">
    <x-module-page-header
        title="Presensi & kehadiran"
        :description="$presensiDesc"
    >
        <x-slot name="actions">
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                <a href="{{ route('presensi.export', request()->query()) }}" class="inline-flex rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Ekspor rekap
                </a>
            @endif
        </x-slot>
    </x-module-page-header>

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input name="tanggal" type="date" value="{{ $filters['tanggal'] ?? '' }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
        @if ($isSiswa && $presensi_jadwals->isNotEmpty())
            <select name="jadwal_id" class="min-w-[200px] rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option value="">Semua sesi</option>
                @foreach ($presensi_jadwals as $jd)
                    <option value="{{ $jd->id }}" @selected(($filters['jadwal_id'] ?? '') == (string) $jd->id)>{{ $jd->mapel }}</option>
                @endforeach
            </select>
        @endif
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
            <span class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-500">Cabang mengikuti data akses</span>
        @endif
        <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option value="">Status — semua</option>
            <option value="hadir" @selected(($filters['status'] ?? '') === 'hadir')>Hadir</option>
            <option value="izin" @selected(($filters['status'] ?? '') === 'izin')>Izin</option>
            <option value="alfa" @selected(($filters['status'] ?? '') === 'alfa')>Alpa</option>
        </select>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Terapkan</button>
        @if ($isSiswa)
            <a href="{{ route('presensi.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
        @endif
    </form>

    <div class="mb-6 grid gap-4 {{ $isSiswa ? 'sm:grid-cols-3' : 'sm:grid-cols-3' }}">
        @if (! empty($summary['siswa_mode']))
            <div class="rounded-xl border border-slate-100 bg-gradient-to-br from-blue-50 to-white p-4 text-blue-950 shadow-sm ring-1 ring-blue-100">
                <p class="text-xs font-semibold uppercase tracking-wide text-blue-700/80">Indeks kehadiran</p>
                <p class="mt-1 text-2xl font-bold">{{ $summary['pct_bulan'] }}%</p>
                <p class="mt-1 text-xs text-blue-800/90">Bulan berjalan ({{ $summary['hadir_bulan_ini'] ?? 0 }}/{{ $summary['total_bulan_ini'] ?? 0 }} sesi)</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sesi pada filter</p>
                <p class="mt-1 text-2xl font-bold text-slate-900">{{ $summary['sesi_tercatat'] ?? 0 }}</p>
                <p class="mt-1 text-xs text-slate-500">Baris yang cocok dengan filter di atas</p>
            </div>
            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Presensi terakhir</p>
                <p class="mt-1 text-xl font-bold text-slate-900">{{ $summary['terakhir_label'] ?? '—' }}</p>
                <p class="mt-1 text-xs text-slate-500">Tanggal entri terbaru</p>
            </div>
        @else
            @foreach ([['Hadir', $summary['hadir'] ?? 0, 'text-emerald-700 bg-emerald-50'], ['Alpa', $summary['alfa'] ?? 0, 'text-rose-700 bg-rose-50'], ['Izin', $summary['izin'] ?? 0, 'text-blue-800 bg-blue-50']] as $stat)
                <div class="rounded-xl border border-slate-100 p-4 {{ $stat[2] }}">
                    <p class="text-xs font-medium opacity-80">{{ $stat[0] }}</p>
                    <p class="mt-1 text-2xl font-bold">{{ $stat[1] }}</p>
                </div>
            @endforeach
        @endif
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        @if ($isSiswa)
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Sesi</th>
                            <th class="px-4 py-3">Tutor</th>
                            <th class="px-4 py-3">Cabang</th>
                            <th class="px-4 py-3">Kehadiran</th>
                        @else
                            <th class="px-4 py-3">Waktu</th>
                            <th class="px-4 py-3">Sesi</th>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Catatan</th>
                            @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                                <th class="px-4 py-3 text-right">Aksi</th>
                            @endif
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($presensis as $p)
                        @php $st = $p->status; @endphp
                        @if ($isSiswa)
                            <tr class="text-slate-700">
                                <td class="px-4 py-3 font-mono text-xs">{{ optional($p->tanggal)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ optional($p->jadwal)->mapel ?? '—' }}</td>
                                <td class="px-4 py-3">{{ optional(optional($p->jadwal)->tutor)->nama ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ optional(optional($p->jadwal)->cabang)->nama_cabang ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold @class([
                                        'bg-emerald-100 text-emerald-800' => $st === 'hadir',
                                        'bg-rose-100 text-rose-800' => $st === 'alfa',
                                        'bg-blue-100 text-blue-800' => $st === 'izin',
                                    ])">{{ ucfirst($st) }}</span>
                                </td>
                            </tr>
                        @else
                            <tr class="text-slate-700">
                                <td class="px-4 py-3 font-mono text-xs">{{ optional($p->tanggal)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ optional($p->jadwal)->mapel }}</td>
                                <td class="px-4 py-3 font-medium">{{ optional($p->siswa)->nama }}</td>
                                <td class="px-4 py-3">Siswa</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold @class([
                                        'bg-emerald-100 text-emerald-800' => $st === 'hadir',
                                        'bg-rose-100 text-rose-800' => $st === 'alfa',
                                        'bg-blue-100 text-blue-800' => $st === 'izin',
                                    ])">{{ ucfirst($st) }}</span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">-</td>
                                @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                                    <td class="px-4 py-3 text-right"><button type="button" class="text-blue-600 hover:underline">Koreksi</button></td>
                                @endif
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="{{ $isSiswa ? 5 : (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']) ? 7 : 6) }}" class="px-4 py-6 text-center text-slate-500">Belum ada data presensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3">{{ $presensis->links() }}</div>
    </div>
</x-layouts.dashboard-shell>
