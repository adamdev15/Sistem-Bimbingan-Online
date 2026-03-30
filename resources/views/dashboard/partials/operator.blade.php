@php
    $isSuper = auth()->user()->hasRole('super_admin');
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

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach ([
        ['title' => 'Total Siswa Aktif', 'value' => '1.284', 'delta' => '+2,4%', 'tone' => 'text-emerald-600', 'icon' => 'users'],
        ['title' => 'Tutor Terdaftar', 'value' => '126', 'delta' => '+3', 'tone' => 'text-emerald-600', 'icon' => 'academic'],
        ['title' => 'Pembayaran Bulan Ini', 'value' => 'Rp 92,3 jt', 'delta' => 'Lunas 78%', 'tone' => 'text-blue-600', 'icon' => 'cash'],
        ['title' => 'Sesi Kelas Hari Ini', 'value' => '36', 'delta' => $isSuper ? '8 cabang' : 'Cabang ini', 'tone' => 'text-slate-600', 'icon' => 'calendar'],
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
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                    @elseif ($card['icon'] === 'academic')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 004.3 2.2c1.82.88 3.77 1.34 5.76 1.34 1.99 0 3.94-.46 5.76-1.34a59.96 59.96 0 004.3-2.2M12 21l-8.5-4.5v-9L12 3l8.5 4.5v9L12 21z"/></svg>
                    @elseif ($card['icon'] === 'cash')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 5.25h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25"/></svg>
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
                    ['label' => 'Data Siswa', 'href' => route('siswa.index'), 'files' => '1.284 aktif', 'date' => 'Update hari ini', 'bg' => 'from-sky-50 to-blue-50', 'border' => 'border-blue-100'],
                    ['label' => 'Jadwal & Kelas', 'href' => route('jadwal.index'), 'files' => '36 sesi', 'date' => 'Minggu ini', 'bg' => 'from-blue-700 to-blue-900', 'border' => 'border-blue-800', 'dark' => true],
                    ['label' => 'Pembayaran', 'href' => route('pembayaran.index'), 'files' => '92 tagihan', 'date' => 'Bulan berjalan', 'bg' => 'from-blue-50 to-indigo-50', 'border' => 'border-indigo-100'],
                ] as $mod)
                    <a href="{{ $mod['href'] }}" class="group flex flex-col rounded-xl border {{ $mod['border'] }} bg-gradient-to-br {{ $mod['bg'] }} p-4 transition hover:shadow-md {{ !empty($mod['dark']) ? 'text-white' : '' }}">
                        <div class="flex items-center justify-between">
                            <span class="rounded-lg {{ !empty($mod['dark']) ? 'bg-white/15' : 'bg-white/80' }} p-2">
                                <svg class="h-5 w-5 {{ !empty($mod['dark']) ? 'text-white' : 'text-blue-700' }}" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.25-2.25h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008z"/></svg>
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
            @if ($isSuper)
                <div class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-3">
                    <a href="{{ route('cabang.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Kelola cabang</a>
                    <a href="{{ route('tutors.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Data tutor</a>
                    <a href="{{ route('presensi.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Presensi</a>
                </div>
            @else
                <div class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-3">
                    <a href="{{ route('tutors.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Tutor cabang</a>
                    <a href="{{ route('presensi.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Presensi</a>
                    <a href="{{ route('cabang.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-center text-sm font-medium text-slate-700 hover:bg-slate-50">Profil cabang</a>
                </div>
            @endif
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
                    <path d="M0 180 L100 150 L200 170 L300 120 L400 140 L500 90 L600 110 L700 60 L800 80 L800 220 L0 220 Z" fill="url(#chartFillOp)" />
                    <path d="M0 180 L100 150 L200 170 L300 120 L400 140 L500 90 L600 110 L700 60 L800 80" fill="none" stroke="rgb(37 99 235)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                    @foreach ([[0,180],[100,150],[200,170],[300,120],[400,140],[500,90],[600,110],[700,60],[800,80]] as $pt)
                        <circle cx="{{ $pt[0] }}" cy="{{ $pt[1] }}" r="5" fill="white" stroke="rgb(37 99 235)" stroke-width="2" />
                    @endforeach
                </svg>
                <div class="mt-2 flex justify-between text-xs text-slate-400">
                    @foreach (['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu'] as $m)
                        <span>{{ $m }}</span>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Capaian operasional</h2>
                <p class="text-sm text-slate-500">KPI (placeholder)</p>
                <ul class="mt-4 space-y-4">
                    @foreach ([
                        ['label' => 'Presensi siswa', 'pct' => 78],
                        ['label' => 'Kelengkapan pembayaran', 'pct' => 64],
                        ['label' => 'Utilisasi tutor', 'pct' => 52],
                    ] as $row)
                        <li>
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-slate-700">{{ $row['label'] }}</span>
                                <span class="text-blue-700">{{ $row['pct'] }}%</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gradient-to-r from-blue-500 to-sky-400" style="width: {{ $row['pct'] }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-semibold text-slate-900">Target vs realisasi</h2>
                <p class="text-sm text-slate-500">Periode berjalan</p>
                <div class="mt-6 flex items-center justify-around gap-4">
                    @foreach ([['label' => '2025', 'pct' => 65], ['label' => '2026', 'pct' => 75]] as $donut)
                        <div class="text-center">
                            <div class="relative mx-auto h-28 w-28">
                                <svg class="-rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                                    <circle cx="18" cy="18" r="15.915" fill="none" stroke="rgb(241 245 249)" stroke-width="4" />
                                    <circle cx="18" cy="18" r="15.915" fill="none" stroke="rgb(37 99 235)" stroke-width="4" stroke-dasharray="{{ $donut['pct'] }}, 100" stroke-linecap="round" />
                                </svg>
                                <span class="absolute inset-0 flex flex-col items-center justify-center text-slate-800">
                                    <span class="text-xl font-bold">{{ $donut['pct'] }}%</span>
                                </span>
                            </div>
                            <p class="mt-2 text-sm font-medium text-slate-600">{{ $donut['label'] }}</p>
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
                        @foreach ([
                            ['Siswa A', 'SPP Bulanan', 'Rp 450.000', 'lunas', '28 Mar 2026'],
                            ['Siswa B', 'Registrasi', 'Rp 150.000', 'lunas', '27 Mar 2026'],
                            ['Siswa C', 'SPP Bulanan', 'Rp 450.000', 'belum', '26 Mar 2026'],
                        ] as $r)
                            <tr class="text-slate-700">
                                <td class="py-3 pr-4 font-medium">{{ $r[0] }}</td>
                                <td class="py-3 pr-4">{{ $r[1] }}</td>
                                <td class="py-3 pr-4">{{ $r[2] }}</td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $r[3] === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $r[3] === 'lunas' ? 'Lunas' : 'Belum' }}
                                    </span>
                                </td>
                                <td class="py-3 text-slate-500">{{ $r[4] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <aside class="space-y-6 xl:col-span-4">
        <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-sky-400 to-blue-600 p-5 text-white shadow-md">
            <div class="flex items-center gap-3">
                <span class="rounded-xl bg-white/20 p-2">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 6m-4.5-6v11.25"/></svg>
                </span>
                <div>
                    <p class="font-semibold">Impor data</p>
                    <p class="text-sm text-sky-100">CSV siswa, jadwal, atau ekspor laporan</p>
                </div>
            </div>
            <button type="button" class="mt-4 w-full rounded-xl bg-white py-3 text-sm font-bold text-blue-700 shadow hover:bg-sky-50">
                Unggah berkas
            </button>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Distribusi pendapatan</h2>
            <p class="text-sm text-slate-500">Per kategori (prototipe)</p>
            <div class="relative mx-auto mt-6 h-44 w-44">
                <div class="absolute inset-0 rounded-full" style="background: conic-gradient(rgb(37 99 235) 0deg 120deg, rgb(14 165 233) 120deg 210deg, rgb(30 64 175) 210deg 300deg, rgb(186 230 253) 300deg 360deg);"></div>
                <div class="absolute inset-[18%] flex items-center justify-center rounded-full bg-white shadow-inner">
                    <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5"/></svg>
                </div>
            </div>
            <ul class="mt-4 grid grid-cols-2 gap-2 text-xs text-slate-600">
                <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-blue-600"></span> SPP</li>
                <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-sky-500"></span> Registrasi</li>
                <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-blue-900"></span> Modul</li>
                <li class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-sky-200"></span> Lainnya</li>
            </ul>
        </div>

        @foreach ([
            ['title' => 'Reminder WA', 'body' => '3 tagihan jatuh tempo besok. Antrian pengiriman siap.', 'from' => 'from-blue-950', 'to' => 'to-blue-800'],
            ['title' => 'Presensi tutor', 'body' => '2 tutor belum konfirmasi sesi malam.', 'from' => 'from-blue-800', 'to' => 'to-blue-600'],
            ['title' => $isSuper ? 'Cabang aktif' : 'Sinkronisasi', 'body' => $isSuper ? '8 cabang online.' : 'Data cabang Anda sinkron.', 'from' => 'from-sky-500', 'to' => 'to-blue-500'],
        ] as $card)
            <div class="rounded-2xl bg-gradient-to-br {{ $card['from'] }} {{ $card['to'] }} p-5 text-white shadow-md">
                <p class="font-semibold">{{ $card['title'] }}</p>
                <p class="mt-2 text-sm leading-relaxed text-blue-50/95">{{ $card['body'] }}</p>
            </div>
        @endforeach
    </aside>
</div>
