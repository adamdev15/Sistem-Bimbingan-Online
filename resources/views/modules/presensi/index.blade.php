<x-layouts.dashboard-shell title="Presensi — eBimbel">
    <x-module-page-header
        title="Presensi & kehadiran"
        description="Rekap per sesi untuk siswa dan konfirmasi kehadiran tutor. Operator dapat memverifikasi; siswa/tutor melihat riwayat."
    >
        <x-slot name="actions">
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                <button type="button" class="inline-flex rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Ekspor rekap
                </button>
            @endif
        </x-slot>
    </x-module-page-header>

    <div class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="date" value="2026-03-31" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
            <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option>Cabang — semua</option>
                <option>Kelapa Gading</option>
            </select>
        @endif
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option>Peran — semua</option>
            <option>Siswa</option>
            <option>Tutor</option>
        </select>
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option>Status — semua</option>
            <option>Hadir</option>
            <option>Terlambat</option>
            <option>Alpa</option>
            <option>Izin</option>
        </select>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-4">
        @foreach ([['Hadir', '186', 'text-emerald-700 bg-emerald-50'], ['Terlambat', '12', 'text-amber-800 bg-amber-50'], ['Alpa', '4', 'text-rose-700 bg-rose-50'], ['Izin', '3', 'text-blue-800 bg-blue-50']] as $stat)
            <div class="rounded-xl border border-slate-100 p-4 {{ $stat[2] }}">
                <p class="text-xs font-medium opacity-80">{{ $stat[0] }}</p>
                <p class="mt-1 text-2xl font-bold">{{ $stat[1] }}</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Sesi</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Peran</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Catatan</th>
                        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                            <th class="px-4 py-3 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ([
                        ['08:05', 'Mat X IPA-2', 'Alya Putri', 'Siswa', 'hadir', '—'],
                        ['08:06', 'Mat X IPA-2', 'Budi S.', 'Siswa', 'terlambat', 'Macet'],
                        ['08:00', 'Mat X IPA-2', 'Andi Wijaya', 'Tutor', 'hadir', '—'],
                    ] as $p)
                        <tr class="text-slate-700">
                            <td class="px-4 py-3 font-mono text-xs">{{ $p[0] }}</td>
                            <td class="px-4 py-3">{{ $p[1] }}</td>
                            <td class="px-4 py-3 font-medium">{{ $p[2] }}</td>
                            <td class="px-4 py-3">{{ $p[3] }}</td>
                            <td class="px-4 py-3">
                                @php $st = $p[4]; @endphp
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold @class([
                                    'bg-emerald-100 text-emerald-800' => $st === 'hadir',
                                    'bg-amber-100 text-amber-800' => $st === 'terlambat',
                                    'bg-rose-100 text-rose-800' => $st === 'alpa',
                                    'bg-blue-100 text-blue-800' => $st === 'izin',
                                ])">{{ ucfirst($st) }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $p[5] }}</td>
                            @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                                <td class="px-4 py-3 text-right"><button type="button" class="text-blue-600 hover:underline">Koreksi</button></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.dashboard-shell>
