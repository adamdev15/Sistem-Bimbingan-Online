@php
    $cabangNama = optional(auth()->user()->cabang)->nama_cabang ?? 'Cabang';
    $jk = $dashboardData['siswa_jenis_kelamin'] ?? ['laki_laki' => 0, 'perempuan' => 0];
    $jkL = (int) ($jk['laki_laki'] ?? 0);
    $jkP = (int) ($jk['perempuan'] ?? 0);
    $jkTotal = max($jkL + $jkP, 1);
    $jkPctL = round(($jkL / $jkTotal) * 100);
    $presensiSeries = $dashboardData['presensi_series'] ?? ['7d' => [], '1m' => [], '1y' => []];
    $presensiRange = $dashboardData['presensi_range'] ?? '7d';
    $presensiActive = collect($presensiSeries[$presensiRange] ?? []);
    $laporanYear = collect($dashboardData['laporan_bulanan_tahun'] ?? []);
    $laporanYearLabel = $dashboardData['laporan_tahun_label'] ?? (string) now()->year;
@endphp

<x-module-page-header
    title="Dashboard Admin Cabang"
    description="Ringkasan operasional cabang Anda: siswa, tutor, jadwal, presensi, dan laporan."
/>

<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4 mb-4">
    @foreach ([
        ['title' => 'Siswa Aktif', 'value' => number_format($dashboardData['total_siswa'] ?? 0), 'tone' => 'text-emerald-600'],
        ['title' => 'Tutor Aktif', 'value' => number_format($dashboardData['total_tutor'] ?? 0), 'tone' => 'text-blue-600'],
        ['title' => 'Sesi Berjalan', 'value' => number_format($dashboardData['sesi_hari_ini'] ?? 0), 'tone' => 'text-indigo-600'],
        ['title' => 'Pendapatan Bulan Ini', 'value' => 'Rp '.number_format((int) ($dashboardData['pembayaran_bulan'] ?? 0), 0, ',', '.'), 'tone' => 'text-slate-700'],
    ] as $card)
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">{{ $card['title'] }}</p>
            <p class="mt-2 text-2xl font-bold text-slate-900">{{ $card['value'] }}</p>
            <p class="mt-1 text-xs font-medium {{ $card['tone'] }}">Data khusus cabang ({{ $cabangNama }})</p>
        </article>
    @endforeach
</div>

<div class="grid gap-4 grid-cols-1 sm:grid-cols-2">
{{-- Grafik: jenis kelamin + presensi --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
                <h2 class="text-lg font-semibold text-slate-900">Siswa aktif — jenis kelamin</h2>
                <p class="mt-1 text-sm text-slate-500">Distribusi di cabang ini</p>
                <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row sm:justify-center sm:gap-10">
                    <div class="relative h-44 w-44 shrink-0">
                        @if ($jkL + $jkP > 0)
                            <div
                                class="h-full w-full rounded-full shadow-inner ring-4 ring-slate-100"
                                style="background: conic-gradient(
                                    rgb(29 78 216) 0deg {{ $jkPctL * 3.6 }}deg,
                                    rgb(56 189 248) {{ $jkPctL * 3.6 }}deg 360deg
                                );"
                                role="img"
                                aria-label="Diagram jenis kelamin"
                            ></div>
                            <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                                <div class="flex h-24 w-24 flex-col items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-200">
                                    <span class="text-2xl font-bold text-slate-900">{{ $jkL + $jkP }}</span>
                                    <span class="text-[10px] font-medium uppercase tracking-wide text-slate-500">siswa</span>
                                </div>
                            </div>
                        @else
                            <div class="flex h-full w-full items-center justify-center rounded-full bg-slate-100 text-sm text-slate-500">Belum ada data</div>
                        @endif
                    </div>
                    <ul class="w-full max-w-xs space-y-3 text-sm">
                        <li class="flex items-center justify-between rounded-lg bg-blue-50 px-3 py-2 ring-1 ring-blue-100">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span class="h-3 w-3 rounded-full bg-blue-700"></span> Laki-laki
                            </span>
                            <span class="font-semibold text-blue-900">{{ number_format($jkL) }}</span>
                        </li>
                        <li class="flex items-center justify-between rounded-lg bg-sky-50 px-3 py-2 ring-1 ring-sky-100">
                            <span class="flex items-center gap-2 font-medium text-slate-700">
                                <span class="h-3 w-3 rounded-full bg-sky-400"></span> Perempuan
                            </span>
                            <span class="font-semibold text-sky-900">{{ number_format($jkP) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Presensi siswa (hadir)</h2>
                        <p class="mt-1 text-sm text-slate-500">Periode dapat difilter</p>
                    </div>
                    <div class="inline-flex rounded-lg border border-slate-200 bg-slate-50 p-0.5 text-xs font-semibold">
                        @foreach (['7d' => '7 hari', '1m' => '1 bulan', '1y' => '1 tahun'] as $key => $label)
                            <a
                                href="{{ route('dashboard', ['presensi_range' => $key]) }}"
                                class="rounded-md px-2.5 py-1.5 transition {{ $presensiRange === $key ? 'bg-white text-blue-700 shadow-sm ring-1 ring-slate-200' : 'text-slate-600 hover:text-slate-900' }}"
                            >{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
                <div class="mt-4 h-56 w-full">
                    @php
                        $pv = $presensiActive->pluck('value');
                        $pMax = max((int) $pv->max(), 1);
                        $pCount = $presensiActive->count();
                        $pPoints = $presensiActive->values()->map(function ($item, $idx) use ($pMax, $pCount) {
                            $x = $pCount > 1 ? (int) round(($idx / ($pCount - 1)) * 800) : 400;
                            $y = 200 - (int) round(((int) $item['value'] / $pMax) * 160);
                            return [$x, $y];
                        })->all();
                        $pLine = collect($pPoints)->map(fn ($p) => "{$p[0]} {$p[1]}")->implode(' L');
                    @endphp
                    <svg viewBox="0 0 800 220" class="h-full w-full overflow-visible" preserveAspectRatio="none" aria-hidden="true">
                        <defs>
                            <linearGradient id="chartFillPresensiCab" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="rgb(37 99 235 / 0.28)" />
                                <stop offset="100%" stop-color="rgb(37 99 235 / 0)" />
                            </linearGradient>
                        </defs>
                        @if ($presensiActive->isNotEmpty())
                            <path d="M{{ $pLine }} L800 220 L0 220 Z" fill="url(#chartFillPresensiCab)" />
                            <path d="M{{ $pLine }}" fill="none" stroke="rgb(37 99 235)" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round" />
                            @foreach ($pPoints as $pt)
                                <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="4.5" fill="white" stroke="rgb(37 99 235)" stroke-width="2" />
                            @endforeach
                        @else
                            <text x="400" y="110" text-anchor="middle" class="fill-slate-400 text-lg" font-size="14">Tidak ada data presensi</text>
                        @endif
                    </svg>
                    <div class="mt-1 flex flex-wrap justify-between gap-1 text-[10px] font-medium text-slate-500 sm:text-xs">
                        @foreach ($presensiActive as $row)
                            <span class="min-w-0 flex-1 text-center leading-tight">{{ $row['label'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-12 mb-5">
    <section class="space-y-6 lg:col-span-8">

        {{-- Laporan: pendapatan per bulan (tahun berjalan) --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">Laporan — pendapatan cabang</h2>
                    <p class="mt-1 text-sm text-slate-500">Agregasi per bulan tahun {{ $laporanYearLabel }}</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full bg-sky-50 px-3 py-1 text-xs font-medium text-sky-900 ring-1 ring-sky-100">
                    <span class="h-2 w-2 rounded-full bg-sky-500"></span> Realisasi pembayaran
                </span>
            </div>
            <div class="mt-6 h-64 w-full">
                @php
                    $lv = $laporanYear->pluck('value');
                    $lMax = max((int) $lv->max(), 1);
                    $lCount = max($laporanYear->count(), 1);
                    $lPoints = $laporanYear->values()->map(function ($item, $idx) use ($lMax, $lCount) {
                        $n = max($lCount - 1, 1);
                        $x = (int) round(($idx / $n) * 800);
                        $y = 200 - (int) round(((int) $item['value'] / $lMax) * 150);
                        return [$x, $y];
                    })->all();
                    $lLine = collect($lPoints)->map(fn ($p) => "{$p[0]} {$p[1]}")->implode(' L');
                @endphp
                <svg viewBox="0 0 800 220" class="h-full w-full overflow-visible" preserveAspectRatio="none" aria-hidden="true">
                    <defs>
                        <linearGradient id="chartFillLaporanCab" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="rgb(14 165 233 / 0.35)" />
                            <stop offset="100%" stop-color="rgb(14 165 233 / 0)" />
                        </linearGradient>
                    </defs>
                    @if ($laporanYear->isNotEmpty())
                        <path d="M{{ $lLine }} L800 220 L0 220 Z" fill="url(#chartFillLaporanCab)" />
                        <path d="M{{ $lLine }}" fill="none" stroke="rgb(2 132 199)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        @foreach ($lPoints as $pt)
                            <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="5" fill="white" stroke="rgb(2 132 199)" stroke-width="2" />
                        @endforeach
                    @endif
                </svg>
                <div class="mt-2 flex justify-between text-xs font-medium text-slate-500">
                    @foreach ($laporanYear as $m)
                        <span>{{ $m['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>

    </section>

    <aside class="space-y-6 lg:col-span-4">
        <div class="rounded-xl border border-blue-100 bg-blue-50 p-5">
            <p class="font-semibold text-blue-900">Kinerja Presensi</p>
            <p class="mt-2 text-sm text-blue-800">Grafik menghitung catatan kehadiran status <strong>hadir</strong> untuk siswa cabang Anda.</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="font-semibold text-slate-900">Catatan Operasional</p>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                <li>- Pastikan jadwal minggu depan sudah final</li>
                <li>- Verifikasi data siswa baru cabang</li>
                <li>- Unduh laporan akhir bulan cabang</li>
            </ul>
        </div>
    </aside>
</div>

<div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Pembayaran terbaru cabang</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="py-3 pr-4">Siswa</th>
                            <th class="py-3 pr-4">Biaya</th>
                            <th class="py-3 pr-4">Nominal</th>
                            <th class="py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach (($dashboardData['pembayaran_terbaru'] ?? collect()) as $payment)
                            <tr>
                                <td class="py-3 pr-4 font-medium text-slate-800">{{ optional($payment->siswa)->nama }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ optional($payment->fee)->nama_biaya }}</td>
                                <td class="py-3 pr-4 text-slate-700">Rp {{ number_format((int) $payment->nominal, 0, ',', '.') }}</td>
                                <td class="py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $payment->status === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $payment->status === 'lunas' ? 'Lunas' : 'Belum' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
