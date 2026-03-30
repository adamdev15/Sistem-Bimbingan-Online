<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Halo, {{ auth()->user()->name }}</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Beranda Siswa</h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Jadwal belajar, kehadiran, dan status pembayaran Anda dalam satu layar.
        </p>
    </div>
    <div class="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm">
        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
        <div>
            <p class="font-semibold text-slate-800">{{ now()->translatedFormat('l, d F Y') }}</p>
            <p class="text-xs text-slate-500">Semester berjalan</p>
        </div>
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([
        ['title' => 'Kehadiran bulan ini', 'value' => '94%', 'sub' => 'Target 85%', 'tone' => 'text-emerald-600'],
        ['title' => 'Tagihan aktif', 'value' => '1', 'sub' => 'Jatuh tempo 5 Apr', 'tone' => 'text-amber-600'],
        ['title' => 'Sesi minggu ini', 'value' => '8', 'sub' => 'Termasuk try out', 'tone' => 'text-blue-600'],
        ['title' => 'Materi saya', 'value' => '12', 'sub' => 'Modul tersimpan', 'tone' => 'text-slate-600'],
    ] as $k)
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">{{ $k['title'] }}</p>
            <p class="mt-2 text-2xl font-bold text-blue-950">{{ $k['value'] }}</p>
            <p class="mt-1 text-xs font-medium {{ $k['tone'] }}">{{ $k['sub'] }}</p>
        </article>
    @endforeach
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-12">
    <div class="space-y-6 lg:col-span-7">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Jadwal besok</h2>
                <a href="{{ route('jadwal.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Lihat semua jadwal</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @foreach ([
                    ['Bahasa Inggris', '09:00', 'Ruang 3B'],
                    ['Kimia', '13:30', 'Lab'],
                ] as $j)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                        <p class="font-semibold text-slate-900">{{ $j[0] }}</p>
                        <span class="text-slate-600">{{ $j[1] }} · {{ $j[2] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
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
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="font-semibold text-amber-900">Pembayaran</p>
            <p class="mt-2 text-sm text-amber-800">SPP April belum dibayar. Bayar online atau ke kasir cabang.</p>
            <a href="{{ route('pembayaran.index') }}" class="mt-4 inline-flex rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700">
                Lihat rincian tagihan
            </a>
        </div>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Aktivitas terkini</h2>
            <ul class="mt-3 space-y-2 text-sm text-slate-600">
                <li>Presensi tercatat — Matematika (30 Mar)</li>
                <li>Materi baru — Fisika Bab 4</li>
                <li>Pembayaran registrasi — Lunas</li>
            </ul>
        </section>
    </aside>
</div>
