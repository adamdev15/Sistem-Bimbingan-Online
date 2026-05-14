<x-layouts.dashboard-shell title="Profil Tutor — Jarimatrik">
    <div class="space-y-6">
        {{-- HEADER --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-900 to-blue-900 px-6 py-8 text-white shadow-2xl">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-blue-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col items-center gap-6 sm:flex-row sm:items-start">
                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-900 to-blue-900 text-4xl font-black text-rose-400 ring-4 ring-blue/10 backdrop-blur-md">
                    {{ strtoupper(substr($tutor->nama, 0, 1)) }}
                </div>
                <div class="text-center sm:text-left">
                    <div class="flex flex-wrap items-center justify-center gap-3 sm:justify-start">
                        <h1 class="text-3xl font-bold tracking-tight">{{ $tutor->nama }}</h1>
                        <span @class([
                            'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest ring-1 ring-inset',
                            'bg-blue-500/20 text-blue-300 ring-blue-500/40' => $tutor->status === 'aktif',
                            'bg-rose-500/20 text-rose-300 ring-rose-500/40' => $tutor->status !== 'aktif',
                        ])>
                            {{ $tutor->status }}
                        </span>
                    </div>
                    <p class="mt-2 text-slate-400">Bergabung {{ $tutor->created_at->translatedFormat('d F Y') }} • Cabang: {{ $tutor->cabangs->pluck('nama_cabang')->implode(', ') }}</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-2 sm:justify-start">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $tutor->no_hp) }}" target="_blank" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700 transition shadow-lg shadow-emerald-900/40">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.038 3.284l-.542 2.317 2.138-.541c.952.613 1.908.995 3.135.996 3.181 0 5.767-2.586 5.768-5.766 0-3.18-2.587-5.766-5.769-5.766zm3.926 8.012c-.145.409-.85.839-1.163.896-.347.063-.71.123-1.148-.107-.439-.23-1.101-.523-1.557-.93-.456-.406-.838-.934-1.121-1.392-.282-.458-.33-.787-.042-1.101.288-.314.439-.387.587-.535.148-.148.19-.254.282-.424.091-.17.042-.314-.021-.458-.063-.144-.542-1.312-.743-1.791-.197-.473-.399-.407-.542-.413-.14-.006-.297-.007-.456-.007s-.408.06-.613.282c-.205.222-.782.764-.782 1.861s.796 2.157.906 2.305c.11.148 1.564 2.506 3.896 3.41.554.215.987.343 1.326.447.555.204 1.061.173 1.46.113.447-.067 1.37-.56 1.564-1.101.194-.542.194-1.006.136-1.101-.057-.095-.213-.145-.456-.263z"/></svg>
                            Kirim Pesan
                        </a>
                        <a href="mailto:{{ $tutor->email }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-800 border border-blue-800 px-4 py-2 text-sm font-bold text-white hover:bg-blue-900 transition">
                            Email
                        </a>
                    </div>
                </div>
                <div class="ml-auto">
                    <a href="{{ route('tutors.index') }}" class="rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold hover:bg-white/20 transition">Kembali</a>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                {{-- INFO UTAMA --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-md">
                    <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-400 mb-6">Data Kepegawaian</h3>
                    <div class="grid gap-6 sm:grid-cols-2">
                        <div class="space-y-1 p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">NIK / Identitas</p>
                            <p class="font-bold text-slate-900">{{ $tutor->nik ?: '—' }}</p>
                        </div>
                        <div class="space-y-1 p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">No HP (WhatsApp)</p>
                            <p class="font-bold text-slate-900">{{ $tutor->no_hp ?: '—' }}</p>
                        </div>
                        <div class="space-y-1 p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jenis Tutor</p>
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider {{ $tutor->jenis_tutor === 'fulltime' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">{{ $tutor->jenis_tutor }}</span>
                        </div>
                        <div class="sm:col-span-2 space-y-1 p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Alamat Lengkap</p>
                            <p class="font-medium text-slate-900">{{ $tutor->alamat ?: '—' }}</p>
                        </div>
                    </div>
                </div>

                {{-- RIWAYAT SESI --}}
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-md">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-black uppercase tracking-[0.2em] text-slate-400">Riwayat Sesi Terakhir</h3>
                        <a href="{{ route('presensi.index', ['tutor_id' => $tutor->id]) }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">Lihat Semua</a>
                    </div>
                    <div class="space-y-4">
                        @php
                            $typeMap = [
                                'full' => 'Sesi Full',
                                'pagi_siang' => 'Shift Pagi-Siang',
                                'siang_sore' => 'Shift Siang-Sore',
                            ];
                        @endphp
                        @forelse($tutor->kehadiranTutors()->latest('tanggal')->limit(5)->get() as $sesi)
                            <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white font-mono text-xs font-bold text-slate-400 shadow-sm ring-1 ring-slate-200">
                                        {{ $sesi->tanggal->format('d') }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900">{{ $typeMap[$sesi->kehadiran] ?? 'Sesi Belajar' }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $sesi->tanggal->translatedFormat('d F Y') }}</div>
                                    </div>
                                </div>
                                @php
                                    $statusColor = match($sesi->status) {
                                        'hadir' => 'bg-emerald-100 text-emerald-700',
                                        'izin' => 'bg-amber-100 text-amber-700',
                                        'sakit' => 'bg-blue-100 text-blue-700',
                                        'alpha' => 'bg-rose-100 text-rose-700',
                                        default => 'bg-slate-100 text-slate-700'
                                    };
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold uppercase {{ $statusColor }}">{{ $sesi->status }}</span>
                            </div>
                        @empty
                            <div class="py-10 text-center">
                                <p class="text-xs text-slate-400 italic">Belum ada riwayat sesi tercatat.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                {{-- STATS CARD --}}
                <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm ring-1 ring-slate-900/5">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6">Performa Mengajar ({{ now()->translatedFormat('F') }})</h3>
                    @php
                        $currentMonthKehadiran = $tutor->kehadiranTutors()
                            ->whereMonth('tanggal', now()->month)
                            ->whereYear('tanggal', now()->year)
                            ->where('status', 'hadir')
                            ->get();

                        $fullCount = $currentMonthKehadiran->where('kehadiran', 'full')->count();
                        $pagiSiangCount = $currentMonthKehadiran->where('kehadiran', 'pagi_siang')->count();
                        $siangSoreCount = $currentMonthKehadiran->where('kehadiran', 'siang_sore')->count();
                        $malamCount = $currentMonthKehadiran->where('kehadiran', 'kelas_malam')->count();

                        // Rumus bobot kehadiran
                        $totalWeighted = ($fullCount * 1.0) + ($pagiSiangCount * 0.5) + ($siangSoreCount * 0.42) + ($malamCount * 1.0);
                    @endphp
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500">Full (100%)</span>
                            <span class="text-sm font-black text-slate-900">{{ $fullCount }} Kali</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500">Pagi-Siang (50%)</span>
                            <span class="text-sm font-black text-slate-900">{{ $pagiSiangCount }} Kali</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500">Siang-Sore (42%)</span>
                            <span class="text-sm font-black text-slate-900">{{ $siangSoreCount }} Kali</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-500">Kelas Malam (100%)</span>
                            <span class="text-sm font-black text-slate-900">{{ $malamCount }} Kali</span>
                        </div>
                        
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-widest text-emerald-600">Total Kehadiran</span>
                            <span class="text-lg font-black text-emerald-700">{{ number_format($totalWeighted, 2) }} Kali</span>
                        </div>
                    </div>
                </div>

                {{-- TIP --}}
                <div class="rounded-2xl bg-gradient-to-br from-blue-900 to-blue-900 p-6 text-white overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 h-24 w-24 rounded-full bg-white/5"></div>
                    <svg class="h-8 w-8 text-rose-500 mb-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path></svg>
                    <p class="text-sm font-medium leading-relaxed">Tutor berdedikasi tinggi. Selalu tepat waktu dalam pengisian presensi dan interaksi dengan siswa sangat baik.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard-shell>
