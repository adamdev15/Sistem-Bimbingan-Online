<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-sm font-medium text-blue-600">Selamat mengajar</p>
        <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Beranda Tutor</h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-600">
            Fokus pada jadwal mengajar, kehadiran kelas, dan siswa bimbingan Anda.
        </p>
    </div>
    <div class="flex shrink-0 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm">
        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25"/></svg>
        <div>
            <p class="font-semibold text-slate-800">{{ now()->translatedFormat('l, d F Y') }}</p>
            <p class="text-xs text-slate-500">Minggu akademik</p>
        </div>
    </div>
</div>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([
        ['title' => 'Sesi hari ini', 'value' => '3', 'sub' => '2 sudah selesai', 'icon' => 'cal'],
        ['title' => 'Siswa aktif', 'value' => '42', 'sub' => 'Di kelas Anda', 'icon' => 'users'],
        ['title' => 'Presensi pending', 'value' => '1', 'sub' => 'Konfirmasi sebelum 18.00', 'icon' => 'check'],
        ['title' => 'Materi minggu ini', 'value' => '5', 'sub' => 'Modul terjadwal', 'icon' => 'book'],
    ] as $k)
        <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
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
                    @elseif ($k['icon'] === 'check')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @else
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                    @endif
                </span>
            </div>
        </article>
    @endforeach
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-12">
    <div class="space-y-6 lg:col-span-7">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900">Jadwal mengajar</h2>
                <a href="{{ route('jadwal.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">Buka kalender penuh</a>
            </div>
            <ul class="divide-y divide-slate-100">
                @foreach ([
                    ['Matematika X IPA-1', '08:00–09:30', 'Ruang A2', 'hijau'],
                    ['Fisika XI IPS', '13:00–14:30', 'Online', 'biru'],
                    ['Try Out Gabungan', '16:00–18:00', 'Aula', 'amber'],
                ] as $s)
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $s[0] }}</p>
                            <p class="text-slate-500">{{ $s[2] }}</p>
                        </div>
                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">{{ $s[1] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Aksi cepat</h2>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <a href="{{ route('presensi.index') }}" class="flex items-center gap-3 rounded-xl border border-blue-100 bg-blue-50/80 p-4 transition hover:border-blue-200">
                    <span class="rounded-lg bg-white p-2 text-blue-700 shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div>
                        <p class="font-semibold text-slate-900">Catat presensi</p>
                        <p class="text-xs text-slate-600">Sesi hari ini & riwayat</p>
                    </div>
                </a>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-slate-300">
                    <span class="rounded-lg bg-slate-100 p-2 text-slate-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </span>
                    <div>
                        <p class="font-semibold text-slate-900">Profil & kontak</p>
                        <p class="text-xs text-slate-600">Update bio pengajar</p>
                    </div>
                </a>
            </div>
        </section>
    </div>
    <aside class="space-y-6 lg:col-span-5">
        <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-indigo-600 to-blue-700 p-5 text-white shadow-md">
            <p class="font-semibold">Pengingat</p>
            <p class="mt-2 text-sm text-indigo-100">Isi rekap kehadiran untuk kelas 08:00 sebelum sistem mengunci otomatis (prototipe).</p>
        </div>
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Siswa perlu perhatian</h2>
            <ul class="mt-3 space-y-3 text-sm">
                @foreach ([['Andi — absen 2x', 'Matematika'], ['Bunga — tugas terlambat', 'Fisika']] as $n)
                    <li class="flex justify-between gap-2 rounded-lg bg-slate-50 px-3 py-2">
                        <span class="font-medium text-slate-800">{{ $n[0] }}</span>
                        <span class="text-slate-500">{{ $n[1] }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    </aside>
</div>
