<x-layouts.dashboard-shell title="Laporan — eBimbel">
    <x-module-page-header
        title="Laporan & analitik"
        description="Ringkasan keuangan, presensi, dan kinerja cabang. Ekspor PDF/Excel akan dihubungkan ke query laporan."
    >
        <x-slot name="actions">
            <button type="button" class="inline-flex rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Unduh PDF
            </button>
            <button type="button" class="inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                Unduh Excel
            </button>
        </x-slot>
    </x-module-page-header>

    <div class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div>
            <label class="block text-xs text-slate-500 mb-1">Dari</label>
            <input type="date" value="2026-03-01" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Sampai</label>
            <input type="date" value="2026-03-31" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option>Jenis laporan — Ringkasan eksekutif</option>
            <option>Pendapatan per cabang</option>
            <option>Presensi siswa</option>
            <option>Utilisasi tutor</option>
        </select>
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option>Cabang — konsolidasi</option>
            <option>Kelapa Gading</option>
            <option>Dago</option>
        </select>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Pendapatan vs bulan lalu</h2>
            <p class="text-sm text-slate-500 mt-1">Maret 2026 (dummy)</p>
            <ul class="mt-4 space-y-3 text-sm">
                @foreach ([['SPP', 'Rp 58 jt', '+4%'], ['Registrasi', 'Rp 12 jt', '−1%'], ['Modul & lainnya', 'Rp 9 jt', '+8%']] as $r)
                    <li class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-700">{{ $r[0] }}</span>
                        <span class="font-medium text-slate-900">{{ $r[1] }}</span>
                        <span class="text-emerald-600">{{ $r[2] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Ranking cabang (hadir %)</h2>
            <ol class="mt-4 space-y-2 text-sm">
                @foreach ([['Kelapa Gading', '96%'], ['Dago', '93%'], ['Ciledug', '89%']] as $idx => $c)
                    <li class="flex justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <span class="font-medium text-slate-800">{{ $idx + 1 }}. {{ $c[0] }}</span>
                        <span class="text-blue-700">{{ $c[1] }}</span>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Detail transaksi (cuplikan)</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">Tanggal</th>
                        <th class="py-2 pr-4">Cabang</th>
                        <th class="py-2 pr-4">Kategori</th>
                        <th class="py-2 pr-4">Jumlah</th>
                        <th class="py-2">Operator</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ([
                        ['30 Mar', 'KG', 'SPP', 'Rp 450.000', 'Kasir A'],
                        ['29 Mar', 'Dago', 'Registrasi', 'Rp 200.000', 'Kasir B'],
                    ] as $t)
                        <tr>
                            <td class="py-3 pr-4">{{ $t[0] }}</td>
                            <td class="py-3 pr-4">{{ $t[1] }}</td>
                            <td class="py-3 pr-4">{{ $t[2] }}</td>
                            <td class="py-3 pr-4">{{ $t[3] }}</td>
                            <td class="py-3">{{ $t[4] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.dashboard-shell>
