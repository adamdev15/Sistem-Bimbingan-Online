<x-layouts.dashboard-shell title="Laporan Keuangan — Bimbel Jarimatrik">
    <x-module-page-header title="Analisa Keuangan" description="Pusat kendali analisa data keuangan bimbel untuk memantau pendapatan dan laba bersih bimbel.">
    </x-module-page-header>

    <div class="space-y-6">
        {{-- FILTER BAR --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-md">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                @if (!$cabangId)
                    <div class="flex-1 min-w-[200px]">
                        <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">Cabang</label>
                        <select name="cabang_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition">
                            @if(auth()->user()->hasRole('super_admin'))
                                <option value="all" @selected($selectedCabangId == 'all')>Semua Cabang</option>
                            @else
                                <option value="">-- Pilih Cabang --</option>
                            @endif
                            @foreach ($cabangs as $c)
                                <option value="{{ $c->id }}" @selected($selectedCabangId == $c->id)>{{ $c->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="flex-1 min-w-[150px]">
                    <label class="mb-1.5 block text-[10px] font-black uppercase tracking-widest text-slate-400">Periode Analisa</label>
                    <input name="month" type="month" value="{{ $month }}" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-700 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-slate-200 transition hover:bg-slate-900 active:scale-95">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Tampilkan Analisa
                </button>
            </form>
        </div>

        {{-- KPI SECTION --}}
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="group relative overflow-hidden rounded-2xl border border-blue-100 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-emerald-50 opacity-50 transition group-hover:scale-110"></div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Total Pemasukan</p>
                <p class="mt-2 text-2xl font-black text-slate-900">Rp {{ number_format($income, 0, ',', '.') }}</p>
                <div class="mt-3 flex items-center gap-1.5 text-[10px] font-bold text-slate-400 truncate">
                    <svg class="h-3 w-3 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    TAGIHAN LUNAS PERIODE INI
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-blue-100 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-50 opacity-50 transition group-hover:scale-110"></div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Biaya Operasional</p>
                <p class="mt-2 text-2xl font-black text-slate-900">Rp {{ number_format($expense, 0, ',', '.') }}</p>
                <div class="mt-3 flex items-center gap-1.5 text-[10px] font-bold text-slate-400 truncate">
                    <svg class="h-3 w-3 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                    BIAYA OPERASIONAL UMUM
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border border-blue-100 bg-white p-5 shadow-sm transition hover:shadow-md">
                <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-indigo-50 opacity-50 transition group-hover:scale-110"></div>
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Honor Guru / Tutor</p>
                <p class="mt-2 text-2xl font-black text-slate-900">Rp {{ number_format($salaries, 0, ',', '.') }}</p>
                <div class="mt-3 flex items-center gap-1.5 text-[10px] font-bold text-slate-400 truncate">
                    <svg class="h-3 w-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    PENGELUARAN GAJI
                </div>
            </div>

            <div class="group relative overflow-hidden rounded-2xl border @if($net < 0) border-rose-200 bg-rose-50 @else border-blue-600 bg-gradient-to-br from-blue-700 to-blue-900 @endif p-5 shadow-lg shadow-blue-200">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] @if($net < 0) text-rose-400 @else text-blue-200 @endif">Laba Bersih Estimasi</p>
                <p class="mt-2 text-2xl font-black @if($net < 0) text-rose-600 @else text-white @endif">Rp {{ number_format($net, 0, ',', '.') }}</p>
                <div class="mt-3 flex items-center gap-1.5 text-[10px] font-black @if($net < 0) text-rose-400 @else text-white/60 @endif">
                    MARGIN AKHIR PERIODE
                </div>
            </div>
        </div>

        @if (!$selectedCabangId && !auth()->user()->hasRole('super_admin'))
            <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-blue-200 bg-blue-50/30 p-20 text-center">
                <div class="mb-6 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-blue-500/10">
                    <svg class="h-12 w-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <h3 class="text-xl font-black text-blue-900 tracking-tight">Menunggu Filtering</h3>
                <p class="mt-2 max-w-sm text-sm text-blue-600 font-medium leading-relaxed">Pilih salah satu cabang di atas dan tentukan periode untuk memulai analisa keuangan mendalam.</p>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-3">
                {{-- CHART --}}
                <div class="lg:col-span-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-md">
                    <div class="flex items-center justify-between mb-8">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Grafik Keuangan Tahunan</h3>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">
                                Analisa Bulanan Periode {{ \Carbon\Carbon::parse($month)->year }}
                                @if($selectedCabangId === 'all')
                                    <span class="text-blue-600">(Semua Cabang)</span>
                                @endif
                            </p>
                        </div>
                        <div class="hidden sm:flex items-center gap-4">
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                <span class="text-[10px] font-black uppercase text-slate-400">Pemasukan</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                                <span class="text-[10px] font-black uppercase text-slate-400">Pengeluaran</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                <span class="text-[10px] font-black uppercase text-slate-400">Laba Bersih</span>
                            </div>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <canvas id="chartFinanceOverview"></canvas>
                    </div>
                </div>

                {{-- NAV TILES --}}
                <div class="space-y-4">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] px-2 mb-4">Navigasi Laporan</h3>
                    
                    <a href="{{ route('laporan-keuangan.harian', ['month' => $month, 'cabang_id' => $selectedCabangId]) }}" class="group relative flex items-center gap-4 overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-lg ">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition group-hover:bg-rose-600 group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-black text-slate-900 leading-none">Buku Kas Harian</p>
                            <p class="mt-1 text-xs text-slate-400 font-medium">Rekap transaksi per hari</p>
                        </div>
                        <svg class="h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    <a href="{{ route('laporan-keuangan.rekap-bulanan', ['month' => $month, 'cabang_id' => $selectedCabangId]) }}" class="group relative flex items-center gap-4 overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-lg">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition group-hover:bg-rose-600 group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-black text-slate-900 leading-none">Laporan Bulanan</p>
                            <p class="mt-1 text-xs text-slate-400 font-medium">Rekapitulasi total periode</p>
                        </div>
                        <svg class="h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </a>

                    <a href="{{ route('laporan-keuangan.bulanan', ['month' => $month, 'cabang_id' => $selectedCabangId]) }}" class="group relative flex items-center gap-4 overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-300 hover:shadow-lg">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition group-hover:bg-rose-600 group-hover:text-white">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-black text-slate-900 leading-none">Administrasi Mitra</p>
                            <p class="mt-1 text-xs text-slate-400 font-medium">Khusus laporan bagi hasil</p>
                        </div>
                        <svg class="h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        @endif
    </div>

    @if ($selectedCabangId)
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" crossorigin="anonymous"></script>
            <script>
                (function () {
                    const chartData = {!! json_encode($chartData ?? ['labels' => [], 'income' => [], 'expense' => [], 'profit' => []]) !!};
                    const ctx = document.getElementById('chartFinanceOverview');
                    if (!ctx || typeof Chart === 'undefined') return;

                    const brand = {
                        income: '#10b981',
                        expense: '#f59e0b',
                        profit: '#2563eb',
                        grid: '#f1f5f9'
                    };

                    function createGradient(chartCtx, color, opacity = 0.1) {
                        const gradient = chartCtx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, color.replace('rgb', 'rgba').replace(')', `, ${opacity})`));
                        gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');
                        return gradient;
                    }

                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: chartData.labels,
                            datasets: [
                                {
                                    label: 'Laba Bersih',
                                    data: chartData.profit,
                                    borderColor: brand.profit,
                                    backgroundColor: 'transparent',
                                    borderWidth: 3,
                                    tension: 0.4,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    z: 3
                                },
                                {
                                    label: 'Pemasukan',
                                    data: chartData.income,
                                    borderColor: brand.income,
                                    backgroundColor: function(context) {
                                        const chart = context.chart;
                                        const {ctx, chartArea} = chart;
                                        if (!chartArea) return null;
                                        return createGradient(ctx, 'rgb(16, 185, 129)', 0.15);
                                    },
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 2,
                                    pointRadius: 4,
                                    pointHoverRadius: 4,
                                    z: 1
                                },
                                {
                                    label: 'Pengeluaran',
                                    data: chartData.expense,
                                    borderColor: brand.expense,
                                    backgroundColor: function(context) {
                                        const chart = context.chart;
                                        const {ctx, chartArea} = chart;
                                        if (!chartArea) return null;
                                        return createGradient(ctx, 'rgb(245, 158, 11)', 0.1);
                                    },
                                    fill: true,
                                    tension: 0.4,
                                    borderWidth: 2,
                                    borderDash: [5, 5],
                                    pointRadius: 4,
                                    pointHoverRadius: 4,
                                    z: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index',
                            },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    padding: 12,
                                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                    titleFont: { size: 14, weight: 'bold' },
                                    bodyFont: { size: 13 },
                                    cornerRadius: 12,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) label += ': ';
                                            if (context.parsed.y !== null) {
                                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: {
                                        font: { size: 11 },
                                        color: '#64748b',
                                        maxRotation: 0,
                                        autoSkip: true,
                                        maxTicksLimit: 10
                                    }
                                },
                                y: {
                                    grid: { color: brand.grid, drawTicks: false },
                                    border: { display: false },
                                    ticks: {
                                        font: { size: 11 },
                                        color: '#94a3b8',
                                        callback: function(value) {
                                            return (value >= 1000000) ? (value / 1000000).toFixed(1) + 'jt' : (value / 1000).toFixed(0) + 'rb';
                                        }
                                    }
                                }
                            }
                        }
                    });
                })();
            </script>
        @endpush
    @endif
</x-layouts.dashboard-shell>
