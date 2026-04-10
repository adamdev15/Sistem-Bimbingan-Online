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
        <p class="text-sm font-medium text-blue-600">{{ $isSuper ? 'Seluruh jaringan cabang' : 'Ringkasan cabang Anda' }}</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
            {{ $isSuper ? 'Dashboard Super Admin' : 'Dashboard Admin Cabang' }}
        </h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Ringkasan operasional: siswa, tutor, jadwal, presensi, pembayaran, dan laporan. Data berikut prototipe UI.
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
    @foreach ([
        ['title' => 'Total Siswa Aktif', 'value' => number_format($dashboardData['total_siswa'] ?? 0), 'delta' => '+2,4%', 'tone' => 'text-emerald-600', 'icon' => 'users'],
        ['title' => 'Total Tutor Terdaftar', 'value' => number_format($dashboardData['total_tutor'] ?? 0), 'delta' => '+3', 'tone' => 'text-emerald-600', 'icon' => 'academic'],
        ['title' => 'Pembayaran Bulan Ini', 'value' => 'Rp '.number_format((int) ($dashboardData['pembayaran_bulan'] ?? 0), 0, ',', '.'), 'delta' => 'Sedang berjalan', 'tone' => 'text-blue-600', 'icon' => 'cash'],
        ['title' => 'Saldo Total '.now()->year, 'value' => 'Rp '.number_format((int) ($moduleCards['saldo_tahun_ini'] ?? 0), 0, ',', '.'), 'delta' => 'Pendapatan Pusat', 'tone' => 'text-emerald-600', 'icon' => 'chart'],
    ] as $card)
        <article class="relative overflow-hidden rounded-xl border border-blue-100/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ $card['title'] }}</p>
                    <p class="mt-2 text-2xl font-bold text-blue-950">{{ $card['value'] }}</p>
                    <p class="mt-1 text-xs font-medium {{ $card['tone'] }}">{{ $card['delta'] }}</p>
                </div>
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                    @if ($card['icon'] === 'users')
                        <div class="p-2 bg-emerald-50 rounded-lg">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    @elseif ($card['icon'] === 'academic')
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 14l9-5-9-5-9 5 9 5z"/><path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                        </div>
                    @elseif ($card['icon'] === 'cash')
                        <div class="p-2 bg-blue-50 rounded-lg">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    @elseif ($card['icon'] === 'chart')
                        <div class="p-2 bg-emerald-50 rounded-lg">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                    @else
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                    @endif
                </span>
            </div>
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
                        <p class="text-sm {{ !empty($mod['dark']) ? 'text-blue-100' : 'text-slate-600' }}">{{ $mod['files'] }}</p>
                        <p class="mt-2 text-xs {{ !empty($mod['dark']) ? 'text-blue-200' : 'text-slate-500' }}">{{ $mod['date'] }}</p>
                    </a>
                @endforeach
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Pendapatan {{ $isSuper ? 'konsolidasi' : 'cabang' }}</h2>
                    <p class="text-sm text-slate-500">Trend 8 bulan (contoh data)</p>
                </div>
                <div class="flex gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-800">
                        <span class="h-2 w-2 rounded-full bg-blue-600"></span> Realisasi
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                        <span class="h-2 w-2 rounded-full bg-slate-400"></span> Target
                    </span>
                </div>
            </div>
            <div class="mt-6 h-64 w-full">
                <svg viewBox="0 0 800 220" class="h-full w-full overflow-visible" preserveAspectRatio="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="chartFillOp" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgb(37 99 235 / 0.35)" />
                            <stop offset="100%" stop-color="rgb(37 99 235 / 0)" />
                        </linearGradient>
                    </defs>
                    @php
                        $vals = $monthlyRevenue->pluck('value');
                        $max = max($vals->max() ?: 1, 1);
                        $points = $monthlyRevenue->values()->map(function ($item, $idx) use ($max) {
                            $x = (int) round(($idx / 7) * 800);
                            $y = 200 - (int) round(($item['value'] / $max) * 150);
                            return [$x, $y];
                        })->all();
                        $line = collect($points)->map(fn ($p) => "{$p[0]} {$p[1]}")->implode(' L');
                    @endphp
                    <path d="M{{ $line }} L800 220 L0 220 Z" fill="url(#chartFillOp)" />
                    <path d="M{{ $line }}" fill="none" stroke="rgb(37 99 235)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    @foreach ($points as $pt)
                        <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="5" fill="white" stroke="rgb(37 99 235)" stroke-width="2" />
                    @endforeach
                </svg>
                <div class="mt-2 flex justify-between text-xs text-slate-400">
                    @foreach ($monthlyRevenue as $m)
                        <span>{{ $m['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2" x-data="{ kpiPeriod: 'month' }">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-slate-900">Capaian operasional</h2>
                    <div class="flex p-1 bg-slate-100 rounded-lg">
                        <button @click="kpiPeriod = 'month'" :class="kpiPeriod === 'month' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'" class="px-3 py-1 text-xs font-bold rounded-md transition-all">Bulan</button>
                        <button @click="kpiPeriod = 'year'" :class="kpiPeriod === 'year' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500'" class="px-3 py-1 text-xs font-bold rounded-md transition-all">Tahun</button>
                    </div>
                </div>
                <p class="text-sm text-slate-500 mt-1">Progress performa berdasarkan filter</p>
                <div class="mt-6 space-y-6">
                    @foreach ([
                        ['label' => 'Presensi Siswa', 'month' => $kpi['presensi']['month'], 'year' => $kpi['presensi']['year'], 'color' => 'from-blue-600 to-sky-400'],
                        ['label' => 'Pembayaran Lunas', 'month' => $kpi['pembayaran']['month'], 'year' => $kpi['pembayaran']['year'], 'color' => 'from-emerald-600 to-teal-400'],
                        ['label' => 'Pengeluaran', 'month' => $kpi['pengeluaran']['month_pct'], 'year' => 100, 'color' => 'from-rose-600 to-orange-400', 'sub' => 'Nominal: Rp '.number_format($kpi['pengeluaran']['month_val'], 0, ',', '.')],
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
                            @if(isset($row['sub']))
                                <p class="mt-1 text-[10px] text-slate-400 font-semibold" x-show="kpiPeriod === 'month'">{{ $row['sub'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Pemasukan VS Pengeluaran</h2>
                <p class="text-sm text-slate-500">Perbandingan realisasi finansial</p>
                <div class="mt-6 space-y-6">
                    @foreach ([['label' => 'Bulan Ini', 'income' => $comparison['income_month'], 'expense' => $comparison['expense_month']], ['label' => 'Tahun Ini', 'income' => $comparison['income_year'], 'expense' => $comparison['expense_year']]] as $comp)
                        @php
                            $total = max($comp['income'] + $comp['expense'], 1);
                            $incPct = round(($comp['income'] / $total) * 100);
                            $expPct = round(($comp['expense'] / $total) * 100);
                        @endphp
                        <div>
                            <div class="flex justify-between text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">
                                <span>{{ $comp['label'] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="h-4 flex-1 overflow-hidden rounded-lg bg-slate-100 flex">
                                    <div class="h-full bg-blue-600 transition-all" style="width: {{ $incPct }}%" title="Income"></div>
                                    <div class="h-full bg-rose-500 transition-all" style="width: {{ $expPct }}%" title="Expense"></div>
                                </div>
                                <span class="text-[10px] font-bold text-slate-700">{{ $incPct }}% / {{ $expPct }}%</span>
                            </div>
                            <div class="mt-1 flex justify-between text-[10px]">
                                <span class="text-blue-600">In: Rp {{ number_format($comp['income'], 0, ',', '.') }}</span>
                                <span class="text-rose-600">Out: Rp {{ number_format($comp['expense'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Pembayaran terbaru</h2>
            <p class="text-sm text-slate-500">Prototipe—nanti dari model Payment</p>
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
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $payment->status === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $payment->status === 'lunas' ? 'Lunas' : 'Belum' }}
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

        <div class="grid gap-6 lg:grid-cols-2">
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
                    <div class="absolute inset-[18%] flex items-center justify-center rounded-full bg-white shadow-inner">
                        <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5"/></svg>
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
                ['title' => 'Pembayaran Lunas', 'body' => $notifications['lunas_today'] . ' Siswa Lunas Pembayaran di hari ini.', 'from' => 'from-blue-800', 'to' => 'to-blue-600', 'icon' => 'check'],
                ['title' => $isSuper ? 'Cabang aktif' : 'Sinkronisasi', 'body' => ($isSuper ? ($notifications['active_cabang'] . ' cabang online.') : 'Data cabang Anda sinkron.'), 'from' => 'from-sky-500', 'to' => 'to-blue-500', 'icon' => 'cloud'],
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
