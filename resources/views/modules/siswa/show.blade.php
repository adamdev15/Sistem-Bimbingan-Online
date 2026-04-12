<x-layouts.dashboard-shell title="Profil Siswa — eBimbel">
    <div class="space-y-6">
        {{-- HEADER / HERO SECTION --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-900 to-blue-900 px-6 py-8 text-white shadow-2xl">
            <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-blue-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 -left-20 h-64 w-64 rounded-full bg-indigo-500/10 blur-3xl"></div>
            
            <div class="relative flex flex-col items-center gap-6 sm:flex-row sm:items-start">
                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-4xl font-black text-white ring-4 ring-white/20 backdrop-blur-md">
                    {{ strtoupper(substr($siswa->nama, 0, 1)) }}
                </div>
                <div class="text-center sm:text-left">
                    <div class="flex flex-wrap items-center justify-center gap-3 sm:justify-start">
                        <h1 class="text-3xl font-bold tracking-tight">{{ $siswa->nama }}</h1>
                        <span @class([
                            'rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-widest ring-1 ring-inset',
                            'bg-emerald-500/20 text-emerald-300 ring-emerald-500/40' => $siswa->status === 'aktif',
                            'bg-rose-500/20 text-rose-300 ring-rose-500/40' => $siswa->status !== 'aktif',
                        ])>
                            {{ $siswa->status }}
                        </span>
                    </div>
                    <p class="mt-2 text-slate-400">NIS: {{ $siswa->nis ?: '—' }} • Terdaftar {{ $siswa->created_at->format('d M Y') }}</p>
                    <div class="mt-4 flex flex-wrap justify-center gap-4 sm:justify-start">
                        <div class="flex items-center gap-2 rounded-lg bg-white/5 px-3 py-1.5 text-sm backdrop-blur-sm">
                            <svg class="h-4 w-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>{{ optional($siswa->cabang)->nama_cabang }}</span>
                        </div>
                    </div>
                </div>
                <div class="ml-auto flex gap-2 sm:self-start">
                    <a href="{{ route('siswa.index') }}" class="rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold hover:bg-white/20 transition">Kembali</a>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            {{-- BIO & INFO --}}
            <div class="lg:col-span-2 space-y-6">
                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-md">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Informasi Pribadi
                    </h2>
                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        <div class="space-y-1 p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">NIK / Identitas</p>
                            <p class="font-bold text-slate-900">{{ $siswa->nik ?: '—' }}</p>
                        </div>
                        <div class="space-y-1 p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Jenis Kelamin</p>
                            <p class="font-bold text-slate-900">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                        </div>
                        <div class="space-y-1 p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">No HP Siswa</p>
                            <p class="font-bold text-slate-900">{{ $siswa->no_hp ?: '—' }}</p>
                        </div>
                         <div class="space-y-1 p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Asal Sekolah</p>
                            <p class="font-bold text-slate-900">{{ $siswa->asal_sekolah ?: '—' }}</p>
                        </div>
                        <div class="sm:col-span-2 space-y-1 p-4 rounded-xl bg-slate-50 border border-slate-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Alamat</p>
                            <p class="font-medium text-slate-900 leading-relaxed">{{ $siswa->alamat ?: '—' }}</p>
                        </div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-900/5 transition hover:shadow-md">
                    <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                        <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Data Keluarga & Wali
                    </h2>
                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        <div class="p-4 rounded-xl border border-dashed border-slate-200">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Nama Ayah</p>
                            <p class="font-bold text-slate-900">{{ $siswa->nama_ayah ?: '—' }}</p>
                            <p class="mt-1 text-xs text-slate-500 italic">{{ $siswa->pekerjaan_ayah ?: 'Pekerjaan —' }}</p>
                        </div>
                        <div class="p-4 rounded-xl border border-dashed border-slate-200">
                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Nama Ibu</p>
                            <p class="font-bold text-slate-900">{{ $siswa->nama_ibu ?: '—' }}</p>
                            <p class="mt-1 text-xs text-slate-500 italic">{{ $siswa->pekerjaan_ibu ?: 'Pekerjaan —' }}</p>
                        </div>
                        <div class="sm:col-span-2 p-4 rounded-xl bg-indigo-50 border border-indigo-100">
                            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-1">Nomor HP Wali</p>
                            <p class="text-lg font-black text-indigo-900">{{ $siswa->no_hp_orang_tua ?: '—' }}</p>
                        </div>
                    </div>
                </section>
            </div>

            {{-- SIDEBAR --}}
            <div class="space-y-6">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm ring-1 ring-slate-900/5">
                    <h3 class="font-black text-slate-900 text-xs uppercase tracking-[0.2em] mb-4">Riwayat Pembayaran</h3>
                    <div class="space-y-4">
                        @forelse($siswa->payments->take(5) as $pay)
                            <div class="flex items-center justify-between border-b border-slate-50 pb-3 last:border-0 last:pb-0">
                                <div>
                                    <p class="text-sm font-bold text-slate-800">{{ optional($pay->fee)->nama_biaya }}</p>
                                    <p class="text-[10px] font-semibold text-slate-400 capitalize">{{ $pay->status }} • {{ optional($pay->tanggal_bayar)->format('d M') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-black text-slate-900">Rp {{ number_format($pay->nominal, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center text-xs text-slate-400 italic">Belum ada riwayat.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl bg-gradient-to-br from-blue-900 to-blue-900 p-6 text-white shadow-lg shadow-blue-200/50">
                    <p class="text-[10px] font-black uppercase tracking-widest text-blue-200">Pembayaran Masuk</p>
                    <p class="mt-2 text-3xl font-black">Rp {{ number_format($siswa->payments->where('status', 'lunas')->sum('nominal'), 0, ',', '.') }}</p>
                    <p class="mt-4 text-[10px] font-medium text-white/60">Total akumulasi pembayaran lunas dari awal pendaftaran.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard-shell>
