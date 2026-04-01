@php
    $cards = $dashboardData['siswa_cards'] ?? [];
    $jadwalBesok = collect($dashboardData['jadwal_besok'] ?? []);
    $pembRincian = collect($dashboardData['pembayaran_rincian'] ?? []);
    $aktivitas = collect($dashboardData['aktivitas_terkini'] ?? []);
    $alert = $dashboardData['pembayaran_alert'] ?? ['ada_tagihan' => false, 'outstanding' => 0, 'pesan' => ''];
@endphp

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Halo, {{ auth()->user()->name }}</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Beranda Siswa</h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Jadwal, kehadiran, dan pembayaran menyesuaikan akun Anda.
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
        ['title' => 'Kehadiran bulan ini', 'value' => ($cards['pct_kehadiran_bulan'] ?? 0).'%', 'sub' => $cards['kehadiran_sub'] ?? '—', 'tone' => 'text-emerald-600'],
        ['title' => 'Tagihan belum lunas', 'value' => number_format($cards['tagihan_belum'] ?? 0), 'sub' => $cards['tagihan_sub'] ?? '—', 'tone' => 'text-amber-600'],
        ['title' => 'Presensi minggu ini', 'value' => number_format($cards['sesi_minggu_ini'] ?? 0), 'sub' => 'Catatan kehadiran tercatat', 'tone' => 'text-blue-600'],
        ['title' => 'Sesi / mapel diikuti', 'value' => number_format($cards['mapel_diikuti'] ?? 0), 'sub' => 'Berdasarkan jadwal Anda', 'tone' => 'text-slate-600'],
    ] as $k)
        <article class="rounded-xl border border-blue-100/80 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <p class="text-sm font-medium text-slate-500">{{ $k['title'] }}</p>
            <p class="mt-2 text-2xl font-bold text-blue-950">{{ $k['value'] }}</p>
            <p class="mt-1 text-xs font-medium {{ $k['tone'] }}">{{ $k['sub'] }}</p>
        </article>
    @endforeach
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-12">
    <div class="space-y-6 lg:col-span-7">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Jadwal besok</h2>
                <a href="{{ route('jadwal.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Semua jadwal saya</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($jadwalBesok as $j)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $j->mapel }}</p>
                            <p class="text-slate-500">{{ optional($j->tutor)->nama ?? '—' }} · {{ optional($j->cabang)->nama_cabang ?? '—' }}</p>
                        </div>
                        <span class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800 ring-1 ring-blue-100">
                            {{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }}
                        </span>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-slate-500">Tidak ada jadwal tercatat untuk hari besok.</li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Pembayaran — rincian</h2>
                <a href="{{ route('pembayaran.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Halaman pembayaran</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse ($pembRincian as $pay)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                        <div>
                            <p class="font-semibold text-slate-900">{{ optional($pay->fee)->nama_biaya ?? 'Biaya' }}</p>
                            <p class="text-slate-500">{{ optional($pay->tanggal_bayar)->translatedFormat('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-slate-900">Rp {{ number_format((int) $pay->nominal, 0, ',', '.') }}</p>
                            <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $pay->status === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $pay->status === 'lunas' ? 'Lunas' : 'Belum' }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-slate-500">Belum ada riwayat pembayaran.</li>
                @endforelse
            </ul>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <h2 class="text-lg font-semibold text-slate-900">Aksi cepat</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                <a href="{{ route('presensi.index') }}" class="rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-semibold text-slate-800 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/50">
                    Presensi
                </a>
                <a href="{{ route('pembayaran.index') }}" class="rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-semibold text-slate-800 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/50">
                    Pembayaran
                </a>
                <a href="{{ route('profile.edit') }}" class="rounded-xl border border-slate-200 bg-white p-4 text-center text-sm font-semibold text-slate-800 shadow-sm transition hover:border-blue-200 hover:bg-blue-50/50">
                    Profil
                </a>
            </div>
        </section>
    </div>

    <aside class="space-y-6 lg:col-span-5">
        <div class="rounded-2xl border p-5 shadow-sm {{ ($alert['ada_tagihan'] ?? false) ? 'border-amber-200 bg-amber-50' : 'border-emerald-200 bg-emerald-50' }}">
            <p class="font-semibold {{ ($alert['ada_tagihan'] ?? false) ? 'text-amber-900' : 'text-emerald-900' }}">Ringkasan tagihan</p>
            <p class="mt-2 text-sm {{ ($alert['ada_tagihan'] ?? false) ? 'text-amber-800' : 'text-emerald-800' }}">{{ $alert['pesan'] ?? '' }}</p>
            <a href="{{ route('pembayaran.index') }}" class="mt-4 inline-flex rounded-lg px-4 py-2 text-sm font-semibold text-white {{ ($alert['ada_tagihan'] ?? false) ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                Lihat rincian
            </a>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 ring-slate-900/5">
            <h2 class="text-lg font-semibold text-slate-900">Aktivitas terkini</h2>
            <ul class="mt-3 space-y-3 text-sm text-slate-600">
                @forelse ($aktivitas as $row)
                    <li class="flex gap-2 rounded-lg bg-slate-50 px-3 py-2">
                        <span class="shrink-0 font-mono text-xs text-slate-400">{{ isset($row['at']) ? $row['at']->translatedFormat('d M') : '—' }}</span>
                        <span class="text-slate-700">{{ $row['teks'] ?? '' }}</span>
                    </li>
                @empty
                    <li class="text-slate-500">Belum ada aktivitas untuk ditampilkan.</li>
                @endforelse
            </ul>
        </section>
    </aside>
</div>
