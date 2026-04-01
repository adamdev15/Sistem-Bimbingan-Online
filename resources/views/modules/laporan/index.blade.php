<x-layouts.dashboard-shell title="Laporan — eBimbel">
    <x-module-page-header
        title="Laporan & analitik"
        description="Ringkasan keuangan, presensi, dan kinerja cabang. Ekspor PDF/Excel akan dihubungkan ke query laporan."
    >
        <x-slot name="actions">
            <a href="{{ route('laporan.export.pdf', request()->query()) }}" class="inline-flex rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Unduh PDF
            </a>
            <a href="{{ route('laporan.export.excel', request()->query()) }}" class="inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                Unduh Excel
            </a>
        </x-slot>
    </x-module-page-header>

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div>
            <label class="block text-xs text-slate-500 mb-1">Dari</label>
            <input name="start_date" type="date" value="{{ optional($start)->format('Y-m-d') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Sampai</label>
            <input name="end_date" type="date" value="{{ optional($end)->format('Y-m-d') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm" disabled>
            <option>Jenis laporan — Ringkasan eksekutif</option>
        </select>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Terapkan</button>
    </form>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Pendapatan vs bulan lalu</h2>
            <p class="text-sm text-slate-500 mt-1">{{ optional($start)->format('d M Y') }} - {{ optional($end)->format('d M Y') }}</p>
            <ul class="mt-4 space-y-3 text-sm">
                @forelse ($paymentByFee as $r)
                    <li class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-700">{{ $r->nama_biaya }}</span>
                        <span class="font-medium text-slate-900">Rp {{ number_format((int) $r->total_nominal, 0, ',', '.') }}</span>
                        <span class="text-emerald-600">Aktif</span>
                    </li>
                @empty
                    <li class="text-slate-500">Belum ada data pembayaran pada periode ini.</li>
                @endforelse
            </ul>
        </section>
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Ranking cabang (hadir %)</h2>
            <ol class="mt-4 space-y-2 text-sm">
                @forelse ($rankingCabang as $idx => $c)
                    <li class="flex justify-between rounded-lg bg-slate-50 px-3 py-2">
                        <span class="font-medium text-slate-800">{{ $idx + 1 }}. {{ $c->nama_cabang }}</span>
                        <span class="text-blue-700">{{ number_format((float) $c->hadir_pct, 1) }}%</span>
                    </li>
                @empty
                    <li class="text-slate-500">Belum ada data ranking cabang.</li>
                @endforelse
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
                    @forelse ($trx as $t)
                        <tr>
                            <td class="py-3 pr-4">{{ optional($t->tanggal_bayar)->format('d M Y') }}</td>
                            <td class="py-3 pr-4">{{ optional(optional($t->siswa)->cabang)->nama_cabang }}</td>
                            <td class="py-3 pr-4">{{ optional($t->fee)->nama_biaya }}</td>
                            <td class="py-3 pr-4">Rp {{ number_format((int) $t->nominal, 0, ',', '.') }}</td>
                            <td class="py-3">{{ optional($t->siswa)->nama }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-6 text-center text-slate-500">Belum ada transaksi pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.dashboard-shell>
