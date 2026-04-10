<x-layouts.dashboard-shell title="Input Presensi — eBimbel">
    <x-module-page-header title="Input Presensi Sesi" description="Catat kehadiran siswa untuk kombinasi tutor, materi, dan waktu sesi.">
        <x-slot name="actions">
            <a href="{{ route('presensi.index') }}" class="inline-flex rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Kembali
            </a>
        </x-slot>
    </x-module-page-header>

    <div x-data="{
        searchSiswa: '',
        selectedMateriId: ''
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

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Tutor <span class="text-red-500">*</span></label>
                        <select name="tutor_id" required class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2.5 outline-none focus:border-blue-500">
                            <option value="">Pilih Tutor</option>
                            @foreach($tutors as $tutor)
                                <option value="{{ $tutor->id }}">{{ $tutor->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Materi Les <span class="text-red-500">*</span></label>
                        <select name="materi_les_id" x-model="selectedMateriId" required class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2.5 outline-none focus:border-blue-500">
                            <option value="">Pilih Materi</option>
                            @foreach($materis as $materi)
                                <option value="{{ $materi->id }}">{{ $materi->nama_materi }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2.5 outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Jam Mulai <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_mulai" required value="15:00" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2.5 outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Jam Selesai <span class="text-red-500">*</span></label>
                        <input type="time" name="jam_selesai" required value="17:00" class="mt-1 w-full rounded-lg border border-slate-200 px-4 py-2.5 outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Daftar Kehadiran Siswa</h2>
                        <p class="text-sm text-slate-500">Pilih status kehadiran. Siswa yang tidak dicentang statusnya tidak akan disimpan riwayatnya di sesi ini.</p>
                    </div>
                    <div>
                        <input type="text" x-model="searchSiswa" placeholder="Cari siswa..." class="px-3 py-2 text-sm border border-slate-200 rounded-lg outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3 font-semibold">Nama Siswa</th>
                                <th class="px-4 py-3 text-center font-semibold">Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($siswas as $siswa)
                                <tr class="hover:bg-slate-50" 
                                    x-show="selectedMateriId != '' && (searchSiswa === '' || '{{ strtolower($siswa->nama) }}'.includes(searchSiswa.toLowerCase())) && '{{ $siswa->materi_les_id }}' == selectedMateriId">
                                    <td class="px-4 py-4 font-medium text-slate-800">{{ $siswa->nama }}</td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:border-emerald-200 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-700">
                                                <input type="radio" :name="'statuses[' + {{ $siswa->id }} + ']'" value="hadir" class="hidden">
                                                <span>Hadir</span>
                                            </label>
                                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:border-blue-200 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-700">
                                                <input type="radio" :name="'statuses[' + {{ $siswa->id }} + ']'" value="izin" class="hidden">
                                                <span>Izin</span>
                                            </label>
                                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:border-amber-200 has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-700">
                                                <input type="radio" :name="'statuses[' + {{ $siswa->id }} + ']'" value="sakit" class="hidden">
                                                <span>Sakit</span>
                                            </label>
                                            <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:border-rose-200 has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 has-[:checked]:text-rose-700">
                                                <input type="radio" :name="'statuses[' + {{ $siswa->id }} + ']'" value="alfa" class="hidden">
                                                <span>Alfa</span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            <tr x-show="selectedMateriId == ''">
                                <td colspan="2" class="px-4 py-8 text-center text-slate-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p>Silakan pilih Materi Les terlebih dahulu untuk menampilkan daftar siswa.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="rounded-xl bg-blue-600 px-8 py-3 text-sm font-bold text-white shadow-sm ring-1 ring-blue-600/20 hover:bg-blue-700 transition">
                        Simpan Presensi
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.dashboard-shell>
