<x-layouts.dashboard-shell title="Input Presensi Siswa — Jarimatrik">
    <x-module-page-header title="Input Presensi Siswa" description="Catat kehadiran siswa berdasarkan materi les dan sesi waktu.">
        <x-slot name="actions">
            <a href="{{ route('presensi.index') }}" class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Kembali
            </a>
        </x-slot>
    </x-module-page-header>

    <div x-data="{
        searchSiswa: '',
        selectedMateriId: '',
        jamMulai: '09:00',
        jamSelesai: '10:00'
    }">
        <form method="POST" action="{{ route('presensi.store-sesi') }}" class="mt-6">
            @csrf

            @if ($errors->any())
                <div class="mb-6 rounded-xl bg-red-50 p-4 border border-red-200">
                    <ul class="list-inside list-disc text-sm text-red-600">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm mb-6 ring-1 ring-slate-900/5">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Materi Les <span class="text-red-500">*</span></label>
                        <select name="materi_les_id" x-model="selectedMateriId" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-medium outline-none focus:border-blue-500 focus:bg-white transition-all">
                            <option value="">Pilih Materi</option>
                            @foreach($materis as $materi)
                                <option value="{{ $materi->id }}">{{ $materi->nama_materi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-medium outline-none focus:border-blue-500 focus:bg-white transition-all">
                    </div>
                    <div class="flex gap-3">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Mulai</label>
                            <input type="time" name="jam_mulai" x-model="jamMulai" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-medium outline-none focus:border-blue-500 focus:bg-white transition-all">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">Selesai</label>
                            <input type="time" name="jam_selesai" x-model="jamSelesai" required class="w-full rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm font-medium outline-none focus:border-blue-500 focus:bg-white transition-all">
                        </div>
                    </div>
                </div>

                <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-6">
                    <div>
                        <h2 class="text-xl font-black text-slate-800">Daftar Kehadiran Siswa</h2>
                        <p class="text-sm text-slate-500">Pilih status kehadiran. Data akan disimpan otomatis untuk siswa yang dipilih.</p>
                    </div>
                    <div class="relative w-full md:w-72">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" x-model="searchSiswa" placeholder="Cari nama siswa..." class="w-full pl-10 pr-4 py-2.5 text-sm border border-slate-200 rounded-xl outline-none focus:border-blue-500 shadow-sm transition-all">
                    </div>
                </div>

                <div class="overflow-hidden border border-slate-200 rounded-2xl shadow-sm">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-[10px] uppercase font-black tracking-widest text-slate-400">
                            <tr>
                                <th class="px-6 py-4">Informasi Siswa</th>
                                <th class="px-6 py-4 text-center">Status Kehadiran</th>
                                <th class="px-6 py-4">Catatan Sesi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($siswas as $siswa)
                                <tr class="hover:bg-slate-50/80 transition-colors" 
                                    x-show="selectedMateriId != '' && (searchSiswa === '' || '{{ strtolower($siswa->nama) }}'.includes(searchSiswa.toLowerCase())) && '{{ $siswa->materi_les_id }}' == selectedMateriId">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-slate-800 text-base">{{ $siswa->nama }}</span>
                                            <span class="text-[10px] text-slate-400 uppercase tracking-tighter">{{ $siswa->materiLes->nama_materi ?? 'TIDAK ADA MATERI' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-1.5">
                                            @foreach(['hadir', 'izin', 'sakit', 'alfa'] as $st)
                                                @php
                                                    $colors = match($st) {
                                                        'hadir' => 'hover:border-emerald-200 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-700',
                                                        'izin' => 'hover:border-blue-200 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-700',
                                                        'sakit' => 'hover:border-amber-200 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-700',
                                                        'alfa' => 'hover:border-rose-200 has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 has-[:checked]:text-rose-700',
                                                    };
                                                @endphp
                                                <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border-2 border-slate-100 bg-white px-3 py-2 text-[10px] font-black uppercase tracking-tight text-slate-400 shadow-sm transition {{ $colors }}">
                                                    <input type="radio" name="statuses[{{ $siswa->id }}]" value="{{ $st }}" class="hidden">
                                                    <span>{{ $st }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <input type="text" name="catatans[{{ $siswa->id }}]" placeholder="Tambahkan catatan..." class="w-full rounded-lg border border-slate-100 bg-slate-50/50 px-3 py-2 text-xs outline-none focus:border-blue-300 focus:bg-white transition-all">
                                    </td>
                                </tr>
                            @endforeach
                            <tr x-show="selectedMateriId == ''">
                                <td colspan="3" class="px-6 py-16 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center space-y-4">
                                        <div class="h-16 w-16 rounded-full bg-slate-50 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        </div>
                                        <div class="max-w-xs mx-auto">
                                            <p class="font-bold text-slate-600">Pilih Materi Terlebih Dahulu</p>
                                            <p class="text-xs leading-relaxed">Silakan tentukan Materi Les pada form di atas untuk memfilter daftar siswa yang sesuai.</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-8 flex items-center justify-between">
                    <div class="flex items-center gap-4 text-slate-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-xs font-medium italic">Pastikan data sudah sesuai sebelum menyimpan.</p>
                    </div>
                    <button type="submit" class="rounded-2xl bg-blue-600 px-10 py-4 text-sm font-black text-white shadow-xl shadow-blue-600/20 ring-1 ring-blue-600/20 hover:bg-blue-700 transition-all active:scale-95">
                        Simpan Semua Presensi
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.dashboard-shell>
