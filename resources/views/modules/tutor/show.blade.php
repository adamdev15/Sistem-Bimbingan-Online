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
                        @forelse($tutor->kehadirans()->with('materiLes')->latest('tanggal')->limit(5)->get() as $sesi)
                            <div class="flex items-center gap-4 p-4 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                                <div class="h-10 w-10 shrink-0 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-slate-900 truncate">{{ optional($sesi->materiLes)->nama_materi }}</p>
                                    <p class="text-xs text-slate-500">{{ $sesi->tanggal->translatedFormat('d F Y') }} • {{ substr($sesi->jam_mulai,0,5) }}-{{ substr($sesi->jam_selesai,0,5) }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-black uppercase text-emerald-700">Selesai</span>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center">
                                <p class="text-sm text-slate-400 italic">Belum ada riwayat sesi mengajar.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                {{-- STATS CARD --}}
                <div class="rounded-2xl bg-white border border-slate-200 p-6 shadow-sm ring-1 ring-slate-900/5">
                    <h3 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 mb-6">Performa Mengajar</h3>
                    <div class="space-y-6">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold text-slate-600">Total Sesi Bulan Ini</span>
                                <span class="text-lg font-black text-slate-900">{{ $tutor->kehadirans()->whereMonth('tanggal', now())->count() }}</span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-slate-100">
                                <div class="h-1.5 rounded-full bg-blue-500" style="width: 65%"></div>
                            </div>
                        </div>
                        <div class="pt-6 border-t border-slate-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Total Penghasilan (Estimasi)</p>
                            <p class="mt-1 text-2xl font-black text-slate-900">Rp {{ number_format($tutor->salaries()->sum('total_gaji'), 0, ',', '.') }}</p>
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
