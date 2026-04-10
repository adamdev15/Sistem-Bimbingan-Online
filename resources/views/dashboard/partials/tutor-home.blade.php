@php
    $cards = $dashboardData['tutor_cards'] ?? [
        'sesi_hari_ini' => 0,
        'siswa_bimbingan' => 0,
        'alfa_hari_ini' => 0,
        'hadir_7_hari' => 0,
    ];
    $jk = $dashboardData['siswa_jenis_kelamin'] ?? ['laki_laki' => 0, 'perempuan' => 0];
    $jkL = (int) ($jk['laki_laki'] ?? 0);
    $jkP = (int) ($jk['perempuan'] ?? 0);
    $jkTotal = max($jkL + $jkP, 1);
    $jkPctL = round(($jkL / $jkTotal) * 100);
    $presensiSeries = $dashboardData['presensi_series'] ?? ['7d' => [], '1m' => [], '1y' => []];
    $presensiRange = $dashboardData['presensi_range'] ?? '7d';
    $presensiActive = collect($presensiSeries[$presensiRange] ?? []);
    $riwayatSesi = collect($dashboardData['riwayat_sesi'] ?? []);
    $siswaPerhatian = collect($dashboardData['siswa_perhatian'] ?? []);
@endphp

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Selamat mengajar</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Beranda Tutor</h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Ringkasan jadwal, siswa bimbingan, dan presensi untuk akun Anda — data otomatis menyesuaikan tutor yang sedang login.
        </p>
    </div>
    <div class="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm">
        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
        <div>
            <p class="font-semibold text-slate-800">{{ now()->translatedFormat('l, d F Y') }}</p>
            <p class="text-xs text-slate-500">{{ config('app.timezone') }}</p>
        </div>
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([
        ['title' => 'Sesi hari ini', 'value' => number_format($cards['sesi_hari_ini'] ?? 0), 'sub' => 'Slot jadwal sesuai hari ini', 'icon' => 'cal'],
        ['title' => 'Siswa bimbingan', 'value' => number_format($cards['siswa_bimbingan'] ?? 0), 'sub' => 'Aktif & pernah hadir di kelas Anda', 'icon' => 'users'],
        ['title' => 'Alpa hari ini', 'value' => number_format($cards['alfa_hari_ini'] ?? 0), 'sub' => 'Perlu ditindaklanjuti', 'icon' => 'alert'],
        ['title' => 'Hadir (7 hari)', 'value' => number_format($cards['hadir_7_hari'] ?? 0), 'sub' => 'Rekap kehadiran positif', 'icon' => 'check'],
    ] as $k)
        <article class="rounded-xl border border-blue-100/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">{{ $k['title'] }}</p>
                    <p class="mt-2 text-2xl font-bold text-blue-950">{{ $k['value'] }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $k['sub'] }}</p>
                </div>
                <span class="rounded-lg bg-blue-50 p-2 text-blue-700">
                    @if ($k['icon'] === 'cal')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
                    @elseif ($k['icon'] === 'users')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372M15 19.128v-2.25M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                    @elseif ($k['icon'] === 'alert')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    @else
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </span>
            </div>
        </article>
    @endforeach
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-12">
    <div class="space-y-6 lg:col-span-7">
        <div class="grid gap-6 lg:grid-cols-1 xl:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
                <h2 class="text-lg font-semibold text-slate-900">Siswa bimbingan — jenis kelamin</h2>
                <p class="mt-1 text-sm text-slate-500">Siswa aktif yang pernah hadir di kelas Anda</p>
                <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row sm:justify-center sm:gap-8">
                    <div class="relative h-40 w-40 shrink-0">
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
                                <div class="flex h-[5.5rem] w-[5.5rem] flex-col items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-200">
                                    <span class="text-xl font-bold text-slate-900">{{ $jkL + $jkP }}</span>
                                    <span class="text-[10px] font-medium uppercase tracking-wide text-slate-500">siswa</span>
                                </div>
                            </div>
                        @else
                            <div class="flex h-full w-full items-center justify-center rounded-full bg-slate-100 text-center text-xs text-slate-500">Belum ada data</div>
                        @endif
                    </div>
                    <ul class="w-full max-w-[220px] space-y-2.5 text-sm">
                        <li class="flex items-center justify-between rounded-lg bg-blue-50 px-3 py-2 ring-1 ring-blue-100">
                            <span class="flex items-center gap-2 font-medium text-slate-700"><span class="h-3 w-3 rounded-full bg-blue-700"></span> Laki-laki</span>
                            <span class="font-semibold text-blue-900">{{ number_format($jkL) }}</span>
                        </li>
                        <li class="flex items-center justify-between rounded-lg bg-sky-50 px-3 py-2 ring-1 ring-sky-100">
                            <span class="flex items-center gap-2 font-medium text-slate-700"><span class="h-3 w-3 rounded-full bg-sky-400"></span> Perempuan</span>
                            <span class="font-semibold text-sky-900">{{ number_format($jkP) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-slate-900">Presensi siswa (hadir)</h2>
                        <p class="mt-1 text-sm text-slate-500">Hanya sesi yang Anda ajar</p>
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
                <div class="mt-4 h-52 w-full">
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
                            <linearGradient id="chartFillPresensiTutor" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="rgb(37 99 235 / 0.28)" />
                                <stop offset="100%" stop-color="rgb(37 99 235 / 0)" />
                            </linearGradient>
                        </defs>
                        @if ($presensiActive->isNotEmpty())
                            <path d="M{{ $pLine }} L800 220 L0 220 Z" fill="url(#chartFillPresensiTutor)" />
                            <path d="M{{ $pLine }}" fill="none" stroke="rgb(37 99 235)" stroke-width="2.75" stroke-linecap="round" stroke-linejoin="round" />
                            @foreach ($pPoints as $pt)
                                <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="4.5" fill="white" stroke="rgb(37 99 235)" stroke-width="2" />
                            @endforeach
                        @else
                            <text x="400" y="110" text-anchor="middle" class="fill-slate-400" font-size="14">Tidak ada data</text>
                        @endif
                    </svg>
                    <div class="mt-1 flex flex-wrap justify-between gap-0.5 text-[10px] font-medium text-slate-500 sm:text-xs">
                        @foreach ($presensiActive as $row)
                            <span class="min-w-0 flex-1 text-center leading-tight">{{ $row['label'] }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Riwayat Sesi Terbaru</h2>
                <a href="{{ route('presensi.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Lihat riwayat lengkap</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($riwayatSesi as $s)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex flex-col items-center justify-center rounded-lg bg-slate-50 border border-slate-100 p-2 min-w-[50px]">
                                <span class="text-[10px] uppercase font-bold text-slate-400">{{ $s['tanggal']->translatedFormat('M') }}</span>
                                <span class="text-lg font-black text-slate-800">{{ $s['tanggal']->translatedFormat('d') }}</span>
                            </div>
                            <div>
                                <p class="font-bold text-slate-900">{{ $s['materi'] }}</p>
                                <p class="text-xs text-slate-500">{{ $s['jam_mulai'] }} – {{ $s['jam_selesai'] }} · {{ $s['cabang'] }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-slate-700">{{ $s['total_siswa'] }} Siswa</p>
                            <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold text-emerald-700 ring-1 ring-emerald-100">
                                {{ $s['hadir'] }} Hadir
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-slate-500">Belum ada riwayat sesi yang tercatat.</li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <h2 class="text-lg font-semibold text-slate-900">Aksi cepat</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <a href="{{ route('presensi.index') }}" class="flex items-center gap-3 rounded-xl border border-blue-100 bg-blue-50/80 p-4 transition hover:border-blue-200">
                    <span class="rounded-lg bg-white p-2 text-blue-700 shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div>
                        <p class="font-semibold text-slate-900">Presensi</p>
                        <p class="text-xs text-slate-600">Riwayat sesi Anda</p>
                    </div>
                </a>
                <a href="{{ route('tutor.siswa.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                    <span class="rounded-lg bg-slate-100 p-2 text-slate-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372M15 19.128v-2.25M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                    </span>
                    <div>
                        <p class="font-semibold text-slate-900">Siswa bimbingan</p>
                        <p class="text-xs text-slate-600">Daftar & filter</p>
                    </div>
                </a>
            </div>
        </section>
    </div>

    <aside class="space-y-6 lg:col-span-5">
        <div class="rounded-2xl border border-blue-800 bg-gradient-to-br from-blue-900 to-blue-700 p-5 text-white shadow-md">
            <p class="font-semibold">Tips</p>
            <p class="mt-2 text-sm text-blue-100">Pastikan presensi diisi konsisten agar grafik dan daftar &ldquo;perlu perhatian&rdquo; akurat untuk siswa Anda.</p>
        </div>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <h2 class="text-lg font-semibold text-slate-900">Siswa perlu perhatian</h2>
            <p class="mt-1 text-xs text-slate-500">Berdasarkan alpa di kelas Anda (30 hari terakhir)</p>
            <ul class="mt-4 space-y-2 text-sm">
                @forelse ($siswaPerhatian as $n)
                    <li class="flex flex-col gap-0.5 rounded-lg border border-amber-100 bg-amber-50/60 px-3 py-2.5 sm:flex-row sm:items-center sm:justify-between">
                        <span class="font-semibold text-slate-900">{{ $n['nama'] }}</span>
                        <span class="text-xs text-amber-900/90 sm:text-right">{{ $n['mapel'] }} · {{ $n['detail'] }}</span>
                    </li>
                @empty
                    <li class="rounded-lg bg-slate-50 px-3 py-4 text-center text-sm text-slate-500">Tidak ada alpa terbaru untuk ditampilkan.</li>
                @endforelse
            </ul>
        </section>
    </aside>
</div>
