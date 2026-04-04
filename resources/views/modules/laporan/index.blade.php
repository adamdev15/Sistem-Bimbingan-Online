<x-layouts.dashboard-shell title="Laporan — eBimbel">
    <x-module-page-header
        title="Laporan & analitik"
        description="Grafik memperbarui langsung saat mengubah dropdown. Tabel ringkasan memakai rentang tanggal di bawah."
    >
        <x-slot name="actions">
            <a href="{{ route('laporan.export.pdf', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700">Ekspor PDF</a>
            <a href="{{ route('laporan.export.excel', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl border border-green-200 bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700">Ekspor Excel</a>
        </x-slot>
    </x-module-page-header>

    <form method="GET" action="{{ route('laporan.index') }}" class="mb-6 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
        @include('modules.laporan.partials.preserve-query', ['except' => ['start_date', 'end_date']])
        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Rentang tabel ringkasan</p>
        <p class="mt-0.5 text-xs text-slate-500">Jenis biaya, ranking cabang, dan cuplikan transaksi.</p>
        <div class="mt-3 flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs text-slate-500">Dari</label>
                <input name="start_date" type="date" value="{{ optional($start)->format('Y-m-d') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs text-slate-500">Sampai</label>
                <input name="end_date" type="date" value="{{ optional($end)->format('Y-m-d') }}" class="rounded-xl border border-slate-200 px-3 py-2 text-sm shadow-sm">
            </div>
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Terapkan tabel</button>
        </div>
    </form>

    <div class="mb-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5 lg:col-span-2">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-bold text-slate-900">{{ $revenue_chart['title'] ?? 'Pendapatan' }}</h2>
                    <p class="mt-2 text-xs font-medium text-blue-800">{{ $revenue_chart['trend'] ?? '' }}</p>
                </div>
                <form method="GET" action="{{ route('laporan.index') }}" id="form-rev" class="flex shrink-0 flex-col gap-2 rounded-xl p-3 sm:min-w-[220px]">
                    @include('modules.laporan.partials.preserve-query', ['except' => ['rev_mode']])
                    <select id="rev_mode" name="rev_mode" onchange="document.getElementById('form-rev').submit()" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm shadow-sm">
                        <option value="minggu" @selected(($rev_mode ?? 'bulan') === 'minggu')>Hari (minggu berjalan)</option>
                        <option value="bulan" @selected(($rev_mode ?? 'bulan') === 'bulan')>Bulan (bulan berjalan)</option>
                        <option value="tahun" @selected(($rev_mode ?? 'bulan') === 'tahun')>Tahun (tahun berjalan)</option>
                    </select>
                </form>
            </div>
            <div class="mt-4 h-72 w-full">
                <canvas id="chartRevenue" aria-label="Grafik pendapatan"></canvas>
            </div>
        </section>


        <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5 lg:col-span-2">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0 flex-1">
                    <h2 class="text-base font-bold text-slate-900">Konversi pembayaran</h2>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $conversion_chart['subtitle'] ?? '' }}</p>
                </div>
                <form method="GET" action="{{ route('laporan.index') }}" id="form-cv" class="flex shrink-0 flex-col gap-2 rounded-xl p-3 sm:min-w-[220px]">
                    @include('modules.laporan.partials.preserve-query', ['except' => ['cv_window']])
                    <select id="cv_window" name="cv_window" onchange="document.getElementById('form-cv').submit()" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-2 text-sm shadow-sm">
                        <option value="bulan" @selected(($cv_window ?? 'bulan') === 'bulan')>1 bulan terakhir</option>
                        <option value="tahun" @selected(($cv_window ?? 'bulan') === 'tahun')>1 tahun terakhir</option>
                    </select>
                </form>
            </div>
            <p class="mt-2 text-xs text-slate-600">Nominal tagihan lunas vs belum lunas (tanggal terbit).</p>
            <div class="mx-auto mt-4 h-64 max-w-xs">
                <canvas id="chartConversion" aria-label="Grafik konversi pembayaran"></canvas>
            </div>
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-base font-semibold text-slate-900">Pendapatan vs jenis biaya</h2>
            <p class="mt-1 text-sm text-slate-500">{{ optional($start)->format('d M Y') }} - {{ optional($end)->format('d M Y') }}</p>
            <ul class="mt-4 space-y-3 text-sm">
                @forelse ($paymentByFee as $r)
                    <li class="flex justify-between border-b border-slate-100 pb-2">
                        <span class="text-slate-700">{{ $r->nama_biaya }}</span>
                        <span class="font-medium text-slate-900">Rp {{ number_format((int) $r->total_nominal, 0, ',', '.') }}</span>
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
                        <th class="py-2">Siswa</th>
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

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
        <script>
            (function () {
                const revenue = @json($revenue_chart ?? ['labels' => [], 'values' => []]);
                const kehadiran = @json($kehadiran_chart ?? ['labels' => [], 'values' => []]);
                const conversion = @json($conversion_chart ?? []);

                const brand = { primary: '#2563eb', emerald: '#059669', amber: '#d97706', rose: '#e11d48', slate: '#64748b' };

                function lineGradient(ctx, chartArea) {
                    if (!chartArea) return brand.primary;
                    const g = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                    g.addColorStop(0, 'rgba(37, 99, 235, 0.08)');
                    g.addColorStop(1, 'rgba(37, 99, 235, 0.35)');
                    return g;
                }

                const revCtx = document.getElementById('chartRevenue');
                if (revCtx && typeof Chart !== 'undefined') {
                    const revData = revenue.values || [];
                    new Chart(revCtx, {
                        type: 'line',
                        data: {
                            labels: revenue.labels || [],
                            datasets: [{
                                label: 'Nominal (Rp)',
                                data: revData,
                                borderColor: brand.primary,
                                backgroundColor: function (context) {
                                    const chart = context.chart;
                                    const { ctx, chartArea } = chart;
                                    if (!chartArea) return 'rgba(37,99,235,0.2)';
                                    return lineGradient(ctx, chartArea);
                                },
                                fill: true,
                                tension: 0.35,
                                borderWidth: 2,
                                pointRadius: revData.length > 20 ? 0 : 3,
                                pointHoverRadius: 5,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { intersect: false, mode: 'index' },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: function (ctx) {
                                            const v = ctx.parsed.y;
                                            return ' Rp ' + Number(v).toLocaleString('id-ID');
                                        },
                                    },
                                },
                            },
                            scales: {
                                x: { grid: { display: false }, ticks: { maxRotation: 45, minRotation: 0 } },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function (value) {
                                            return 'Rp ' + Number(value).toLocaleString('id-ID');
                                        },
                                    },
                                },
                            },
                        },
                    });
                }

                const khCtx = document.getElementById('chartKehadiran');
                if (khCtx && typeof Chart !== 'undefined') {
                    const khKind = kehadiran.chart_kind || '';
                    if (khKind === 'stacked_bar' && Array.isArray(kehadiran.datasets) && kehadiran.datasets.length && (kehadiran.labels || []).length) {
                        new Chart(khCtx, {
                            type: 'bar',
                            data: {
                                labels: kehadiran.labels || [],
                                datasets: kehadiran.datasets.map(function (ds) {
                                    return {
                                        label: ds.label,
                                        data: ds.data,
                                        backgroundColor: ds.backgroundColor,
                                    };
                                }),
                            },
                            options: {
                                indexAxis: 'y',
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: { stacked: true, beginAtZero: true, ticks: { precision: 0 } },
                                    y: { stacked: true },
                                },
                                plugins: {
                                    legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 10 } } },
                                },
                            },
                        });
                    }
                }

                const convCtx = document.getElementById('chartConversion');
                if (convCtx && typeof Chart !== 'undefined') {
                    const lun = Number(conversion.lunas_nominal || 0);
                    const bel = Number(conversion.belum_nominal || 0);
                    new Chart(convCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Lunas', 'Belum lunas'],
                            datasets: [{
                                data: [lun, bel],
                                backgroundColor: [brand.emerald, brand.amber],
                                borderWidth: 0,
                                hoverOffset: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '58%',
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 12 } },
                                tooltip: {
                                    callbacks: {
                                        label: function (ctx) {
                                            const v = typeof ctx.raw === 'number' ? ctx.raw : 0;
                                            return ' Rp ' + Number(v).toLocaleString('id-ID');
                                        },
                                    },
                                },
                            },
                        },
                    });
                }
            })();
        </script>
    @endpush
</x-layouts.dashboard-shell>
