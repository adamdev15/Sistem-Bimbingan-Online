@php
    $isSuper = auth()->user()->hasRole('super_admin');
    $moduleCards = $dashboardData['module_cards'] ?? ['siswa' => 0, 'materi_les' => 0, 'tagihan' => 0, 'saldo_tahun_ini' => 0];
    $monthlyRevenue = collect($dashboardData['monthly_revenue'] ?? []);
    $kpi = $dashboardData['kpi'] ?? ['presensi' => ['month' => 0, 'year' => 0], 'pembayaran' => ['month' => 0, 'year' => 0], 'pengeluaran' => ['month_val' => 0, 'year_val' => 0, 'month_pct' => 0]];
    $dist = collect($dashboardData['distribution'] ?? []);
    $comparison = $dashboardData['income_vs_expense'] ?? ['income_month' => 0, 'expense_month' => 0, 'income_year' => 0, 'expense_year' => 0];
    $notifications = $dashboardData['notifications'] ?? ['wa_reminder' => 0, 'lunas_today' => 0, 'active_cabang' => 0];
@endphp
{{-- Page intro --}}
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
            {{ $isSuper ? 'Dashboard Super Admin' : 'Dashboard Admin Cabang' }}
        </h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Ringkasan operasional seluruh cabang untuk memantau siswa, pendapatan, dan pembayaran secara terpusat.
        </p>
    </div>
    <div class="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm">
        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
        <div>
            <p class="font-semibold text-slate-800">{{ now()->translatedFormat('l, d F Y') }}</p>
            <p class="text-xs text-slate-500">Zona waktu App: {{ config('app.timezone') }}</p>
        </div>
    </div>
</div>

<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
    @php
        $cards = [
            [
                'title' => 'Total Siswa Aktif', 
                'value' => number_format($dashboardData['total_siswa'] ?? 0), 
                'delta' => 'Naik +2,4%', 
                'sub' => 'Bulan lalu',
                'trend' => 'up',
                'tone' => 'text-emerald-600', 
                'icon' => 'users'
            ],
            [
                'title' => 'Cabang Terdaftar', 
                'value' => number_format($dashboardData['total_cabang'] ?? 0), 
                'delta' => 'Naik +3%', 
                'sub' => 'Berjalan '.($dashboardData['total_cabang'] ?? 0),
                'trend' => 'up',
                'tone' => 'text-emerald-600', 
                'icon' => 'academic'
            ],
            [
                'title' => 'Pendapatan Bulan Ini', 
                'value' => 'Rp '.number_format((int) ($dashboardData['pembayaran_bulan'] ?? 0), 0, ',', '.'), 
                'delta' => 'Seluruh cabang', 
                'sub' => '',
                'trend' => 'up',
                'tone' => 'text-blue-600', 
                'icon' => 'chart',
                'bg' => 'from-blue-700 to-blue-900',
                'border' => 'border-blue-800',
                'dark' => true
            ],
            [
                'title' => 'Pengeluaran Bulan Ini', 
                'value' => 'Rp '.number_format((int) ($dashboardData['kpi']['pengeluaran']['month_val'] ?? 0), 0, ',', '.'), 
                'delta' => 'Operasional & Lainnya', 
                'sub' => '',
                'trend' => 'down',
                'tone' => 'text-amber-600', 
                'icon' => 'cash',
                'bg' => 'from-blue-400 to-blue-600',
                'border' => 'border-blue-600',
                'dark' => true
            ],
            
        ];
    @endphp

    @foreach ($cards as $card)
        <article class="relative overflow-hidden rounded-xl border 
            {{ $card['border'] ?? 'border-blue-100/80' }} 
            {{ !empty($card['dark']) ? 'bg-gradient-to-br '.$card['bg'].' text-white' : 'bg-white' }} 
            p-5 shadow-sm ring-1 ring-slate-900/5 transition-all hover:shadow-md group">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest 
                        {{ !empty($card['dark']) ? 'text-white' : 'text-blue-500' }}">{{ $card['title'] }}</p>
                    <p class="mt-2 text-xl font-black 
                        {{ !empty($card['dark']) ? 'text-white' : 'text-blue-950' }}">{{ $card['value'] }}</p>
                    <div class="mt-2 flex items-center gap-1">
                        <span class="inline-flex items-center gap-0.5 rounded-md px-1.5 py-0.5 text-[9px] font-bold {{ $card['trend'] === 'up' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                            @if($card['trend'] === 'up')
                                <svg class="h-3 w-3 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5l15-15m0 0H8.25m11.25 0v11.25"/></svg>
                            @else
                                <svg class="h-3 w-3 transition-transform group-hover:-translate-x-0.5 group-hover:translate-y-0.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 4.5l-15 15m0 0h11.25m-11.25 0V8.25"/></svg>
                            @endif
                            {{ $card['delta'] }}
                        </span>
                        <span class="text-[10px] font-medium text-slate-400 capitalize">{{ $card['sub'] }}</span>
                    </div>
                </div>
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl 
                    {{ !empty($card['dark']) 
                        ? 'bg-white/10 text-white' 
                        : ($card['trend'] === 'up' ? 'bg-blue-50 text-blue-600' : 'bg-rose-50 text-rose-600') 
                    }} shadow-inner">
                    @if ($card['icon'] === 'users')
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    @elseif ($card['icon'] === 'academic')
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @elseif ($card['icon'] === 'cash')
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    @elseif ($card['icon'] === 'chart')
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </span>
            </div>
            <div class="absolute -bottom-2 -right-2 h-16 w-16 rounded-full bg-slate-50 opacity-10 transition-transform group-hover:scale-110"></div>
        </article>
    @endforeach
</div>

<div class="mt-8 grid gap-6 xl:grid-cols-12">
    <div class="space-y-6 xl:col-span-8">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Akses modul</h2>
                <a href="{{ route('laporan.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Laporan & ringkasan</a>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['label' => 'Data Siswa', 'href' => route('siswa.index'), 'files' => number_format($moduleCards['siswa'] ?? 0).' aktif', 'date' => 'Update hari ini', 'bg' => 'from-sky-50 to-blue-50', 'border' => 'border-blue-100'],
                    ['label' => 'Saldo Tahun Ini', 'href' => route('laporan-keuangan.index'), 'files' => 'Rp '.number_format($moduleCards['saldo_tahun_ini'] ?? 0, 0, ',', '.'), 'date' => 'Laba bersih '.now()->year, 'bg' => 'from-blue-700 to-blue-900', 'border' => 'border-blue-800', 'dark' => true],
                    ['label' => 'Pembayaran', 'href' => route('pembayaran.index'), 'files' => number_format($moduleCards['tagihan'] ?? 0).' tagihan', 'date' => 'Belum lunas', 'bg' => 'from-blue-50 to-indigo-50', 'border' => 'border-indigo-100'],
                ] as $mod)
                    <a href="{{ $mod['href'] }}" class="group flex flex-col rounded-xl border {{ $mod['border'] }} bg-gradient-to-br {{ $mod['bg'] }} p-4 transition hover:shadow-md {{ !empty($mod['dark']) ? 'text-white' : '' }}">
                        <div class="flex items-center justify-between">
                            <span class="rounded-lg {{ !empty($mod['dark']) ? 'bg-white/10 ring-1 ring-white/20' : 'bg-white/80 ring-1 ring-black/5' }} p-2.5">
                                @if($mod['label'] === 'Data Siswa')
                                    <svg class="h-6 w-6 {{ !empty($mod['dark']) ? 'text-white' : 'text-blue-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                @elseif($mod['label'] === 'Saldo Tahun Ini')
                                    <svg class="h-6 w-6 {{ !empty($mod['dark']) ? 'text-white' : 'text-blue-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @else
                                    <svg class="h-6 w-6 {{ !empty($mod['dark']) ? 'text-white' : 'text-blue-600' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                @endif
                            </span>
                            <span class="flex -space-x-2">
                                @foreach (range(1, 3) as $i)
                                    <span class="inline-block h-7 w-7 rounded-full border-2 {{ !empty($mod['dark']) ? 'border-blue-800 bg-blue-500' : 'border-white bg-blue-200' }} text-[10px] leading-7 text-center font-bold {{ !empty($mod['dark']) ? 'text-white' : 'text-blue-900' }}">{{ $i }}</span>
                                @endforeach
                            </span>
                        </div>
                        <p class="mt-3 font-semibold {{ !empty($mod['dark']) ? 'text-white' : 'text-slate-900' }}">{{ $mod['label'] }}</p>
                        <p class="text-md {{ !empty($mod['dark']) ? 'text-blue-100' : 'text-slate-600' }}">{{ $mod['files'] }}</p>
                        <p class="mt-2 text-xs {{ !empty($mod['dark']) ? 'text-blue-200' : 'text-slate-500' }}">{{ $mod['date'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            x-data="{
                filter: 'monthly',
                labels: [],
                pemasukan: [],
                pengeluaran: [],
                total: { pemasukan: 0, pengeluaran: 0, persentase_pemasukan: 0 },
                loading: false,
                months: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
                async fetchData() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/dashboard/keuangan-chart?filter=${this.filter}`);
                        const data = await response.json();
                        this.labels = data.labels;
                        this.pemasukan = data.pemasukan;
                        this.pengeluaran = data.pengeluaran;
                        this.total = data.total;
                    } catch (e) {
                        console.error('Failed to fetch chart data', e);
                    }
                    this.loading = false;
                },
                get currentPeriodLabel() {
                    const now = new Date();
                    return this.filter === 'monthly' 
                        ? 'Bulan ' + this.months[now.getMonth()] + ' ' + now.getFullYear()
                        : 'Tahun ' + now.getFullYear();
                },
                get max() {
                    const allVals = [...this.pemasukan, ...this.pengeluaran];
                    return Math.max(...allVals, 1);
                },
                get incomePoints() {
                    if (this.pemasukan.length === 0) return [];
                    const n = this.pemasukan.length;
                    return this.pemasukan.map((val, idx) => {
                        const x = 45 + (idx / (n - 1)) * 755;
                        const y = 200 - (val / this.max) * 150;
                        return [x, y, val];
                    });
                },
                get expensePoints() {
                    if (this.pengeluaran.length === 0) return [];
                    const n = this.pengeluaran.length;
                    return this.pengeluaran.map((val, idx) => {
                        const x = 45 + (idx / (n - 1)) * 755;
                        const y = 200 - (val / this.max) * 150;
                        return [x, y, val];
                    });
                },
                get incomeLine() {
                    const points = this.incomePoints;
                    if (points.length === 0) return '';
                    return 'M ' + points.map(p => p[0] + ' ' + p[1]).join(' L');
                },
                get expenseLine() {
                    const points = this.expensePoints;
                    if (points.length === 0) return '';
                    return 'M ' + points.map(p => p[0] + ' ' + p[1]).join(' L');
                },
                formatRupiah(val) {
                    return 'Rp ' + Number(val).toLocaleString('id-ID');
                },
                formatShort(val) {
                    if (val === 0) return '0';
                    if (val >= 1000000) return (val / 1000000).toFixed(1) + 'jt';
                    if (val >= 1000) return (val / 1000).toFixed(0) + 'k';
                    return val;
                }
            }"
            x-init="fetchData()"
        >
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="space-y-1">
                    <h2 class="text-lg font-semibold text-slate-900">Grafik Pemasukan dan Pengeluaran Bimbel</h2>
                    <p class="text-sm text-slate-500">Tren Keuangan <span x-text="currentPeriodLabel"></span></p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-[10px] font-bold text-blue-600 border border-blue-100">
                            <span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span> Pemasukan
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-[10px] font-bold text-amber-600 border border-amber-100">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Pengeluaran
                        </span>
                    </div>
                    <div class="flex p-1 bg-slate-100 rounded-lg">
                        <button @click="filter = 'monthly'; fetchData()" :class="filter === 'monthly' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'" class="px-3 py-1 text-xs font-bold rounded-md transition-all">Bulan ini</button>
                        <button @click="filter = 'yearly'; fetchData()" :class="filter === 'yearly' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'" class="px-3 py-1 text-xs font-bold rounded-md transition-all">Tahun ini</button>
                    </div>
                </div>
            </div>

            <div class="transition-opacity duration-300" :class="loading ? 'opacity-50' : 'opacity-100'">
                <div class="h-64 w-full relative">
                    <svg viewBox="0 0 800 220" class="h-full w-full overflow-visible" preserveAspectRatio="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="chartFillIncome" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="rgb(14 165 233 / 0.35)" />
                            <stop offset="100%" stop-color="rgb(14 165 233 / 0)" />
                            </linearGradient>
                            <linearGradient id="chartFillExpense" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="rgb(245 158 11 / 0.35)" />
                                <stop offset="100%" stop-color="rgb(245 158 11 / 0)" />
                            </linearGradient>
                        </defs>

                        <!-- Pemasukan Path -->
                        <path :d="incomeLine + ' L 800 200 L 45 200 Z'" fill="url(#chartFillIncome)" x-show="incomeLine" />
                        <path :d="incomeLine" fill="none" stroke="rgb(2 132 199)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" x-show="incomeLine" />
                        
                        <!-- Pengeluaran Path -->
                        <path :d="expenseLine + ' L 800 200 L 45 200 Z'" fill="url(#chartFillExpense)" x-show="expenseLine" />
                        <path :d="expenseLine" fill="none" stroke="rgb(245 158 11)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" x-show="expenseLine" />
 
                        <!-- Dots & Labels Income -->
                        <template x-for="(pt, idx) in incomePoints" :key="'inc-'+idx">
                            <g x-show="pt[2] > 0">
                                <circle :cx="pt[0]" :cy="pt[1]" r="5" fill="white" stroke="rgb(2 132 199)" stroke-width="2.5" />
                                <text :x="pt[0]" :y="pt[1] - 12" text-anchor="middle" 
                                      style="font-family: sans-serif; font-size: 12px; font-weight: 900; fill: rgb(7 89 133);">
                                    <tspan x-text="formatRupiah(pt[2])"></tspan>
                                </text>
                            </g>
                        </template>
 
                        <!-- Dots & Labels Expense -->
                        <template x-for="(pt, idx) in expensePoints" :key="'exp-'+idx">
                            <g x-show="pt[2] > 0">
                                <circle :cx="pt[0]" :cy="pt[1]" r="5" fill="white" stroke="rgb(234 88 12)" stroke-width="2.5" />
                                <text :x="pt[0]" :y="pt[1] + 22" text-anchor="middle" 
                                      style="font-family: sans-serif; font-size: 12px; font-weight: 900; fill: rgb(154 52 18);">
                                    <tspan x-text="formatRupiah(pt[2])"></tspan>
                                </text>
                            </g>
                        </template>
                    </svg>

                    <!-- X-Axis Labels -->
                    <div class="flex justify-between text-[10px] font-bold text-slate-400 pl-[45px]">
                        <template x-for="(label, idx) in labels" :key="idx">
                            <span x-show="filter === 'yearly' || idx % 2 === 0 || idx === labels.length - 1" 
                                  x-text="label" 
                                  class="w-8 text-center"></span>
                        </template>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2" x-data="{ kpiPeriod: 'month' }">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Capaian operasional Bimbel</h2>
                    <div class="flex p-1 bg-slate-100 rounded-lg">
                        <button @click="kpiPeriod = 'month'" :class="kpiPeriod === 'month' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'" class="px-3 py-1 text-xs font-bold rounded-md transition-all">Bulan</button>
                        <button @click="kpiPeriod = 'year'" :class="kpiPeriod === 'year' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'" class="px-3 py-1 text-xs font-bold rounded-md transition-all">Tahun</button>
                    </div>
                </div>
                <p class="text-sm text-slate-500 mt-1">Progress performa berdasarkan filter</p>
                <div class="mt-6 space-y-6">
                    @foreach ([
                        ['label' => 'Laba Bersih Pendapatan', 'month' => ($comparison['income_month'] > 0 ? round((max($comparison['income_month'] - $comparison['expense_month'], 0) / $comparison['income_month']) * 100) : 0), 'year' => ($comparison['income_year'] > 0 ? round((max($comparison['income_year'] - $comparison['expense_year'], 0) / $comparison['income_year']) * 100) : 0), 'color' => 'from-blue-600 to-blue-400', 'year_sub' => 'Laba: Rp '.number_format(max($comparison['income_year'] - $comparison['expense_year'], 0), 0, ',', '.'), 'month_sub' => 'Laba: Rp '.number_format(max($comparison['income_month'] - $comparison['expense_month'], 0), 0, ',', '.')],
                        ['label' => 'Pembayaran Lunas', 'month' => $kpi['pembayaran']['month'], 'year' => $kpi['pembayaran']['year'], 'color' => 'from-emerald-600 to-teal-400', 'month_sub' => 'Nominal: Rp '.number_format($kpi['pembayaran']['month_val'], 0, ',', '.'), 'year_sub' => 'Nominal: Rp '.number_format($kpi['pembayaran']['year_val'], 0, ',', '.')],
                        ['label' => 'Biaya Pengeluaran', 'month' => $kpi['pengeluaran']['month_pct'], 'year' => 100, 'color' => 'from-rose-600 to-orange-400', 'month_sub' => 'Nominal: Rp '.number_format($kpi['pengeluaran']['month_val'], 0, ',', '.'), 'year_sub' => 'Nominal: Rp '.number_format($kpi['pengeluaran']['year_val'], 0, ',', '.')],
                    ] as $row)
                        <div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-bold text-slate-800">{{ $row['label'] }}</span>
                                <span class="text-blue-700 font-bold" x-text="kpiPeriod === 'month' ? '{{ $row['month'] }}%' : '{{ $row['year'] }}%'"></span>
                            </div>
                            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-100 flex">
                                <div class="h-full rounded-full bg-gradient-to-r {{ $row['color'] }} shadow-sm transition-all duration-500" 
                                     :style="'width: ' + (kpiPeriod === 'month' ? '{{ $row['month'] }}%' : '{{ $row['year'] }}%')"></div>
                            </div>
                            @if(isset($row['month_sub']))
                                <p class="mt-1 text-[10px] text-slate-400 font-semibold">
                                    <span x-show="kpiPeriod === 'month'">{{ $row['month_sub'] }}</span>
                                    <span x-show="kpiPeriod === 'year'">{{ $row['year_sub'] }}</span>
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                @php
                    $materiDist = $dashboardData['siswa_materi_les'] ?? [];
                    $totalSiswaMateri = array_sum($materiDist);
                    $safeTotalMateri = max($totalSiswaMateri, 1);
                    
                    $materiConfigs = [
                        'Les Matematika Intensif' => ['color' => 'rgb(56 189 248)', 'bg' => 'bg-sky-50', 'ring' => 'ring-sky-100', 'text' => 'text-sky-900', 'dot' => 'bg-sky-400'],
                        'Les Kelas Prima' => ['color' => 'rgb(251 191 36)', 'bg' => 'bg-amber-50', 'ring' => 'ring-amber-100', 'text' => 'text-amber-900', 'dot' => 'bg-amber-400'],
                        'Les Jarimatika' => ['color' => 'rgb(239 68 68)', 'bg' => 'bg-red-50', 'ring' => 'ring-red-100', 'text' => 'text-red-900', 'dot' => 'bg-red-500'],
                        'Les Baca AHE' => ['color' => 'rgb(234 88 12)', 'bg' => 'bg-orange-50', 'ring' => 'ring-orange-100', 'text' => 'text-orange-900', 'dot' => 'bg-orange-600'],
                        'Les IEC' => ['color' => 'rgb(30 58 138)', 'bg' => 'bg-indigo-50', 'ring' => 'ring-indigo-100', 'text' => 'text-indigo-900', 'dot' => 'bg-indigo-900'],
                    ];

                    $gradientStops = [];
                    $currentAngle = 0;
                    foreach ($materiConfigs as $label => $config) {
                        $val = $materiDist[$label] ?? 0;
                        $pct = ($val / $safeTotalMateri) * 100;
                        $angle = ($pct / 100) * 360;
                        if ($pct > 0) {
                            $gradientStops[] = "{$config['color']} {$currentAngle}deg " . ($currentAngle + $angle) . "deg";
                        }
                        $currentAngle += $angle;
                    }
                    $conicGradient = !empty($gradientStops) ? implode(', ', $gradientStops) : 'rgb(241 245 249) 0deg 360deg';
                @endphp

                <h2 class="text-lg font-semibold text-slate-900">Grafik Siswa per Materi Les</h2>
                <p class="text-sm text-slate-500">Distribusi seluruh siswa bimbingan</p>
                
                <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row sm:justify-center">
                    <div class="relative h-40 w-40 shrink-0">
                        @if ($totalSiswaMateri > 0)
                            <div
                                class="h-full w-full rounded-full shadow-inner ring-4 ring-slate-50"
                                style="background: conic-gradient({{ $conicGradient }});"
                            ></div>
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <div class="flex h-24 w-24 flex-col items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-200">
                                    <span class="text-2xl font-bold text-slate-900">{{ number_format($totalSiswaMateri) }}</span>
                                    <span class="text-[10px] font-medium uppercase tracking-wide text-slate-500">Total</span>
                                </div>
                            </div>
                        @else
                            <div class="flex h-full w-full items-center justify-center rounded-full bg-slate-100 text-sm text-slate-500">Belum ada data</div>
                        @endif
                    </div>
                    <ul class="w-full max-w-[240px] space-y-2 text-sm">
                        @foreach ($materiConfigs as $label => $config)
                            @php
                                $val = $materiDist[$label] ?? 0;
                            @endphp
                            <li class="relative flex items-center justify-between rounded-lg {{ $config['bg'] }} px-3 py-1.5 ring-1 {{ $config['ring'] }}">
                                <span class="flex items-center gap-2 font-medium text-slate-700">
                                    <span class="h-3 w-3 rounded-full {{ $config['dot'] }}"></span> {{ $label }}
                                </span>
                                <span class="font-semibold {{ $config['text'] }}">{{ number_format($val) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Pembayaran terbaru bimbel</h2>
            <p class="text-sm text-slate-500">Aktivitas Transaksi Pembayaran Siswa Terbaru</p>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="py-3 pr-4">Siswa</th>
                            <th class="py-3 pr-4">Biaya</th>
                            <th class="py-3 pr-4">Nominal</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach (($dashboardData['pembayaran_terbaru'] ?? collect()) as $payment)
                            <tr class="text-slate-700">
                                <td class="py-3 pr-4 font-medium">{{ optional($payment->siswa)->nama }}</td>
                                <td class="py-3 pr-4">{{ optional($payment->fee)->nama_biaya }}</td>
                                <td class="py-3 pr-4">Rp {{ number_format((int) $payment->nominal, 0, ',', '.') }}</td>
                                <td class="py-3 pr-4">
                                    @php
                                        $isLunas = $payment->status === 'lunas';
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-wider {{ $isLunas ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $isLunas ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                        {{ $isLunas ? 'Lunas' : 'Belum Lunas' }}
                                    </span>
                                </td>
                                <td class="py-3 text-slate-500">{{ optional($payment->tanggal_bayar)->format('d M Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <aside class="space-y-6 xl:col-span-4">

        <div class="grid gap-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Distribusi pendapatan</h2>
                <p class="text-sm text-slate-500">Berdasarkan kategori biaya</p>
                <div class="relative mx-auto mt-6 h-44 w-44 rounded-full">
                    @php
                        $totalDist = $dist->sum('value') ?: 1;
                        $dashArray = 0;
                        $colors = ['#2563eb', '#0ea5e9', '#1e40af', '#bae6fd', '#3b82f6'];
                    @endphp
                    <svg class="-rotate-90" viewBox="0 0 36 36">
                        <circle cx="18" cy="18" r="15.915" fill="none" stroke="#f1f5f9" stroke-width="5" />
                        @foreach($dist as $idx => $item)
                            @php
                                $pct = ($item['value'] / $totalDist) * 100;
                                $offset = 100 - $dashArray;
                                $dashArray += $pct;
                                $angle = ($dashArray - ($pct / 2)) * 3.6; // In degrees
                                $rad = deg2rad($angle);
                                $labelR = 12;
                                $lx = 18 + $labelR * cos($rad);
                                $ly = 18 + $labelR * sin($rad);
                            @endphp
                            <circle cx="18" cy="18" r="15.915" fill="none" stroke="{{ $colors[$idx % count($colors)] }}" stroke-width="5" stroke-dasharray="{{ $pct }} {{ 100 - $pct }}" stroke-dashoffset="{{ $offset }}" stroke-linecap="butt" />
                            @if($pct > 5)
                                <text x="{{ $lx }}" y="{{ $ly }}" transform="rotate(90 {{ $lx }} {{ $ly }})" fill="black" font-size="2.2" font-weight="bold" text-anchor="middle" dominant-baseline="middle">{{ round($pct) }}%</text>
                            @endif
                        @endforeach
                    </svg>
                    <div class="absolute inset-[20%] flex items-center justify-center rounded-full bg-white shadow-inner">
                        <svg class="h-16 w-16 text-blue-600 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5"/></svg>
                    </div>
                </div>
                <ul class="mt-4 space-y-1.5 text-xs text-slate-600">
                    @foreach($dist as $idx => $item)
                        <li class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full" style="background: {{ $colors[$idx % count($colors)] }}"></span>
                                <span class="truncate max-w-[100px]">{{ $item['label'] }}</span>
                            </div>
                            <span class="font-bold">Rp {{ number_format($item['value'], 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                    @if($dist->isEmpty())
                        <li class="text-center text-slate-400 py-4 italic">Belum ada data distribusi</li>
                    @endif
                </ul>
            </div>
            
            <div>
            @foreach ([
                ['title' => 'Reminder WA', 'body' => $notifications['wa_reminder'] . ' tagihan jatuh tempo besok. Antrian pengiriman siap.', 'from' => 'from-blue-950', 'to' => 'to-blue-800', 'icon' => 'bell'],
                ['title' => 'Pembayaran Lunas', 'body' => $notifications['lunas_month'] . ' Siswa Lunas Pembayaran di bulan ini.', 'from' => 'from-blue-800', 'to' => 'to-blue-600', 'icon' => 'check'],
                ['title' => $isSuper ? 'Cabang aktif' : 'Sinkronisasi', 'body' => ($isSuper ? ($notifications['active_cabang'] . ' cabang terdaftar.') : 'Data cabang Anda sinkron.'), 'from' => 'from-sky-500', 'to' => 'to-blue-500', 'icon' => 'cloud'],
            ] as $card)
                <div class="group relative rounded-2xl bg-gradient-to-br {{ $card['from'] }} {{ $card['to'] }} p-5 text-white shadow-md mb-2 overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-2">
                             @if($card['icon'] === 'bell')
                                <svg class="h-4 w-4 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                             @elseif($card['icon'] === 'check')
                                <svg class="h-4 w-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                             @else
                                <svg class="h-4 w-4 text-sky-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                             @endif
                            <p class="font-bold text-xs uppercase tracking-widest opacity-80">{{ $card['title'] }}</p>
                        </div>
                        <p class="text-sm leading-relaxed text-blue-50/95 font-medium">{{ $card['body'] }}</p>
                    </div>
                    {{-- Abstract Pattern --}}
                    <div class="absolute -right-4 -bottom-4 h-24 w-24 rounded-full bg-white/10 blur-2xl group-hover:bg-white/20 transition-all"></div>
                </div>
            @endforeach
            </div>
        </div>
    </aside>
</div>
