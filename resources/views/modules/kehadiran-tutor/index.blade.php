@php
    $isSuperAdmin = auth()->user()->hasRole('super_admin');
@endphp
<x-layouts.dashboard-shell title="Absensi Tutor — Bimbel Jarimatrik">
    <x-module-page-header title="Absensi Kehadiran Tutor" description="Kelola data kehadiran tutor per sesi (Full, Pagi-Siang, Siang-Sore).">
        <x-slot name="actions">
            <button @click="$dispatch('open-modal', 'modal-report')" class="inline-flex items-center gap-2 rounded-xl border border-rose-500 bg-rose-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak Laporan
            </button>
            <button @click="$dispatch('open-modal', 'modal-add')" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 hover:bg-blue-700 transition-all">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Input Absensi
            </button>
        </x-slot>
    </x-module-page-header>

    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 p-5">
        {{-- FILTER --}}
        <form method="GET" class="flex flex-wrap items-end gap-3 mb-6 p-4 border-b border-slate-100">
            @if($isSuperAdmin)
                <div class="w-48">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cabang</label>
                    <select name="cabang_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300">
                        <option value="">Semua Cabang</option>
                        @foreach ($cabangs as $c)
                            <option value="{{ $c->id }}" @selected(request('cabang_id') == $c->id)>{{ $c->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="w-48">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Bulan</label>
                <input name="month" type="month" value="{{ request('month') ?? date('Y-m') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300">
            </div>
            <div class="w-48">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300">
                    <option value="">Semua Status</option>
                    <option value="hadir" @selected(request('status') == 'hadir')>Hadir</option>
                    <option value="izin" @selected(request('status') == 'izin')>Izin</option>
                    <option value="alpha" @selected(request('status') == 'alpha')>Alfa</option>
                    <option value="sakit" @selected(request('status') == 'sakit')>Sakit</option>
                </select>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari Tutor</label>
                <input name="search" type="text" value="{{ request('search') }}" placeholder="Ketik nama tutor..." class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Filter</button>
                <a href="{{ route('kehadiran-tutor.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Reset</a>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Tanggal</th>
                        @if($isSuperAdmin) <th class="px-4 py-3">Cabang</th> @endif
                        <th class="px-4 py-3">Nama Tutor</th>
                        <th class="px-4 py-3">Kehadiran</th>
                        <th class="px-4 py-3">Jam</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Catatan</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($kehadirans as $k)
                        <tr class="text-slate-700 hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-400">{{ $loop->iteration + ($kehadirans->firstItem() - 1) }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-slate-900">{{ $k->tanggal->format('d/m/Y') }}</td>
                            @if($isSuperAdmin) <td class="px-4 py-3 text-xs font-bold text-slate-900">{{ $k->cabang->nama_cabang }}</td> @endif
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $k->tutor->nama }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-[10px] font-bold uppercase">{{ str_replace('_', '-', $k->kehadiran) }}</span>
                            </td>
                            <td class="px-4 py-3 text-xs">{{ substr($k->jam_mulai, 0, 5) }} - {{ $k->jam_selesai ? substr($k->jam_selesai, 0, 5) : '—' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $stCfg = match($k->status) {
                                        'hadir' => ['color' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'dot' => 'bg-emerald-500'],
                                        'alpha'   => ['color' => 'bg-rose-50 text-rose-700 border-rose-100', 'dot' => 'bg-rose-500'],
                                        'izin'   => ['color' => 'bg-blue-50 text-blue-700 border-blue-100', 'dot' => 'bg-blue-500'],
                                        'sakit'  => ['color' => 'bg-amber-50 text-amber-700 border-amber-100', 'dot' => 'bg-amber-500'],
                                        default => ['color' => 'bg-slate-50 text-slate-700 border-slate-100', 'dot' => 'bg-slate-500'],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-wider border {{ $stCfg['color'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $stCfg['dot'] }}"></span>
                                    {{ $k->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $k->catatan ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="$dispatch('open-modal-edit', {{ $k }}); $dispatch('open-modal', 'modal-edit')" class="p-1.5 text-slate-400 text-yellow-500 hover:text-yellow-600 transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form action="{{ route('kehadiran-tutor.destroy', $k) }}" method="POST" onsubmit="event.preventDefault(); confirmDelete(this, 'Hapus absensi ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-600 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isSuperAdmin ? 9 : 8 }}" class="px-4 py-8 text-center text-slate-500">Belum ada data kehadiran tutor.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($kehadirans->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
                {{ $kehadirans->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL ADD --}}
    <x-modal name="modal-add" title="Input Absensi Tutor">
        <form action="{{ route('kehadiran-tutor.store') }}" method="POST" class="p-6 space-y-4" x-data="{
            cabangId: '{{ $cabangId }}',
            tutors: @js($tutors),
            kehadiran: 'full',
            tutorSearch: '',
            get filteredTutors() {
                let list = this.tutors.filter(t => t.cabang_id == this.cabangId);
                if (this.tutorSearch) {
                    list = list.filter(t => t.nama.toLowerCase().includes(this.tutorSearch.toLowerCase()));
                }
                return list;
            },
            get jamMulai() {
                if (this.kehadiran === 'full' || this.kehadiran === 'pagi_siang') return '09:00';
                if (this.kehadiran === 'siang_sore') return '13:30';
                return '09:00';
            },
            get jamSelesai() {
                if (this.kehadiran === 'full' || this.kehadiran === 'siang_sore') return '17:00';
                if (this.kehadiran === 'pagi_siang') return '11:30';
                return '17:00';
            }
        }">
            @csrf
            @if($isSuperAdmin)
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Pilih Cabang</label>
                    <select name="cabang_id" x-model="cabangId" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none">
                        <option value="">Pilih Cabang</option>
                        @foreach($cabangs as $c)
                            <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" name="cabang_id" value="{{ $cabangId }}">
            @endif

            <div x-data="{ 
                tutorOpen: false,
                selectedTutors: [],
                init() {
                    this.$watch('cabangId', () => {
                        this.selectedTutors = [];
                    });
                },
                toggleTutor(id) {
                    if (this.selectedTutors.includes(id)) {
                        this.selectedTutors = this.selectedTutors.filter(i => i !== id);
                    } else {
                        this.selectedTutors.push(id);
                    }
                }
            }">
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Pilih Tutor (Bisa Lebih dari 1)</label>
                <div class="relative">
                    <!-- Trigger -->
                    <button type="button" @click="tutorOpen = !tutorOpen" 
                        class="relative w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-left text-sm shadow-sm focus:border-blue-500 outline-none transition-all min-h-[46px]">
                        <div class="flex flex-wrap gap-1.5 pr-8">
                            <template x-if="selectedTutors.length === 0">
                                <span class="text-slate-400">Pilih satu atau lebih tutor...</span>
                            </template>
                            <template x-for="id in selectedTutors" :key="id">
                                <span class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                    <span x-text="tutors.find(t => t.id == id)?.nama"></span>
                                    <svg @click.stop="toggleTutor(id)" class="h-3 w-3 cursor-pointer hover:text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </span>
                            </template>
                        </div>
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </span>
                    </button>

                    <!-- Dropdown -->
                    <div x-show="tutorOpen" x-cloak @click.outside="tutorOpen = false" 
                        class="absolute z-[60] mt-2 max-h-80 w-full overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/10 transition-all border border-slate-100">
                        <div class="p-2 border-b border-slate-50 bg-slate-50/50">
                            <div class="relative">
                                <input type="text" x-model="tutorSearch" placeholder="Cari nama tutor..." class="w-full rounded-xl border border-slate-200 bg-white pl-9 pr-4 py-2 text-xs font-semibold focus:border-blue-500 outline-none transition-all">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>
                        <div class="max-h-60 overflow-y-auto p-2 space-y-1 scrollbar-thin scrollbar-thumb-slate-200">
                            <template x-if="filteredTutors.length === 0">
                                <p class="p-2 text-xs text-slate-500 text-center">Tidak ada tutor di cabang ini.</p>
                            </template>
                            <template x-for="t in filteredTutors" :key="t.id">
                                <div @click="toggleTutor(t.id)" 
                                    class="flex items-center gap-3 cursor-pointer rounded-lg px-3 py-2 text-sm transition-colors hover:bg-slate-50"
                                    :class="selectedTutors.includes(t.id) ? 'bg-blue-50/50' : ''">
                                    <div class="flex h-4 w-4 items-center justify-center rounded border transition-colors"
                                        :class="selectedTutors.includes(t.id) ? 'bg-blue-600 border-blue-600' : 'border-slate-300 bg-white'">
                                        <svg x-show="selectedTutors.includes(t.id)" class="h-3 w-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="block font-medium text-slate-700 truncate" x-text="t.nama"></span>
                                            <span class="text-[9px] font-black uppercase tracking-widest text-slate-400 bg-slate-100 px-1.5 py-0.5 rounded" x-text="t.jenis_tutor || 'Parttime'"></span>
                                        </div>
                                        <span class="block text-[10px] text-slate-400 uppercase tracking-tight" x-text="t.cabang?.nama_cabang || 'Cabang tidak diketahui'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Hidden Inputs -->
                    <template x-for="id in selectedTutors" :key="'tutor-input-'+id">
                        <input type="hidden" name="tutor_ids[]" :value="id">
                    </template>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Tanggal</label>
                    <input type="date" name="tanggal" required value="{{ date('Y-m-d') }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Kehadiran</label>
                    <select name="kehadiran" x-model="kehadiran" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none">
                        <option value="full">Full Day</option>
                        <option value="pagi_siang">Pagi - Siang (09:00-11:30)</option>
                        <option value="siang_sore">Siang - Sore (13:30-17:00)</option>
                        <option value="kelas_malam">Kelas Malam</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Jam Mulai</label>
                    <input type="time" name="jam_mulai" :value="jamMulai" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Jam Selesai</label>
                    <input type="time" name="jam_selesai" :value="jamSelesai" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Status Kehadiran</label>
                <div class="flex flex-wrap gap-2">
                    <template x-for="st in ['hadir', 'izin', 'sakit', 'alpha']">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:border-slate-300 transition-all"
                            :class="{
                                'has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-700': st === 'hadir',
                                'has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-700': st === 'izin',
                                'has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-700': st === 'sakit',
                                'has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 has-[:checked]:text-rose-700': st === 'alpha',
                            }">
                            <input type="radio" name="status" :value="st" class="hidden" :checked="st === 'hadir'">
                            <span x-text="st.charAt(0).toUpperCase() + st.slice(1)"></span>
                        </label>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Catatan (Opsional)</label>
                <textarea name="catatan" rows="2" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none placeholder:text-slate-400" placeholder="Tambahkan catatan jika ada..."></textarea>
            </div>

            <div class="pt-4 flex items-center gap-3">
                <button type="button" @click="$dispatch('close-modal', 'modal-add')" class="w-full rounded-xl border border-slate-200 px-6 py-3.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" class="w-full rounded-xl bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">Simpan Absensi</button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL EDIT --}}
    <x-modal name="modal-edit" title="Edit Absensi Tutor">
        <form :action="'{{ url('kehadiran-tutor') }}/' + editData.id" method="POST" class="p-6 space-y-4" x-data="{
            editData: {},
            kehadiran: 'full',
            init() {
                window.addEventListener('open-modal-edit', (e) => {
                    this.editData = e.detail;
                    this.kehadiran = this.editData.kehadiran;
                });
            }
        }">
            @csrf @method('PUT')
            <div class="p-4 rounded-xl bg-blue-50 border border-slate-200 mb-4">
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Tutor</p>
                <div class="flex items-center justify-between">
                    <p class="text-sm font-black text-slate-900" x-text="editData.tutor?.nama"></p>
                    <span class="text-[9px] font-black uppercase tracking-widest text-white bg-blue-400 px-1.5 py-0.5 rounded" x-text="editData.tutor?.jenis_tutor || 'Parttime'"></span>
                </div>
                <p class="text-[10px] text-slate-400" x-text="editData.cabang?.nama_cabang"></p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Tanggal</label>
                    <input type="date" name="tanggal" :value="editData.tanggal ? editData.tanggal.substring(0, 10) : ''" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Kehadiran</label>
                    <select name="kehadiran" x-model="kehadiran" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none">
                        <option value="full">Full Day</option>
                        <option value="pagi_siang">Pagi - Siang</option>
                        <option value="siang_sore">Siang - Sore</option>
                        <option value="kelas_malam">Kelas Malam</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Jam Mulai</label>
                    <input type="time" name="jam_mulai" :value="editData.jam_mulai ? editData.jam_mulai.substring(0, 5) : ''" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Jam Selesai</label>
                    <input type="time" name="jam_selesai" :value="editData.jam_selesai ? editData.jam_selesai.substring(0, 5) : ''" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Status Kehadiran</label>
                <div class="flex flex-wrap gap-2">
                    <template x-for="st in ['hadir', 'izin', 'sakit', 'alpha']">
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-xl border-2 border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:border-slate-300 transition-all"
                            :class="{
                                'has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-700': st === 'hadir',
                                'has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50 has-[:checked]:text-blue-700': st === 'izin',
                                'has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 has-[:checked]:text-amber-700': st === 'sakit',
                                'has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 has-[:checked]:text-rose-700': st === 'alpha',
                            }">
                            <input type="radio" name="status" :value="st" class="hidden" :checked="editData.status === st">
                            <span x-text="st.charAt(0).toUpperCase() + st.slice(1)"></span>
                        </label>
                    </template>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-700 mb-1.5">Catatan (Opsional)</label>
                <textarea name="catatan" x-text="editData.catatan" rows="2" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none placeholder:text-slate-400" placeholder="Tambahkan catatan jika ada..."></textarea>
            </div>

            <div class="pt-4 flex items-center gap-3">
                <button type="button" @click="$dispatch('close-modal', 'modal-edit')" class="w-full rounded-xl border border-slate-200 px-6 py-3.5 text-sm font-bold text-slate-700 hover:bg-slate-50 transition">Batal</button>
                <button type="submit" class="w-full rounded-xl bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg transition hover:bg-blue-800">Simpan Perubahan</button>
            </div>
        </form>
    </x-modal>

    {{-- MODAL REPORT --}}
    <x-modal name="modal-report" title="Cetak Laporan Kehadiran Tutor" maxWidth="md">
        <form action="{{ route('kehadiran-tutor.export') }}" method="GET" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Format Laporan</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="inline-flex cursor-pointer items-center justify-center gap-3 rounded-2xl border-2 border-slate-200 bg-white p-4 text-sm font-bold text-slate-600 shadow-sm transition hover:border-rose-200 has-[:checked]:border-rose-500 has-[:checked]:bg-rose-50 has-[:checked]:text-rose-700">
                            <input type="radio" name="type" value="pdf" class="hidden" checked>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10.5h1v3h-1v-3zM9 10.5h1v3H9v-3zM10.5 10.5h1v3h-1v-3z"/><path fill-rule="evenodd" d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8l-6-6zM6 20V4h7v5h5v11H6z" clip-rule="evenodd"/></svg>
                            PDF
                        </label>
                        <label class="inline-flex cursor-pointer items-center justify-center gap-3 rounded-2xl border-2 border-slate-200 bg-white p-4 text-sm font-bold text-slate-600 shadow-sm transition hover:border-emerald-200 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-700">
                            <input type="radio" name="type" value="excel" class="hidden">
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M14.5 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V7.5L14.5 2zM12 18H8v-2h4v2zm0-4H8v-2h4v2zm0-4H8V8h4v2zm5 8h-3v-2h3v2zm0-4h-3v-2h3v2zm0-4h-3V8h3v2z"/></svg>
                            Excel
                        </label>
                    </div>
                </div>

                @if($isSuperAdmin)
                    <div class="col-span-2">
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Pilih Cabang</label>
                        <select name="cabang_id" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none shadow-sm">
                            <option value="">Semua Cabang</option>
                            @foreach($cabangs as $c)
                                <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-span-2">
                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Pilih Periode (Bulan-Tahun)</label>
                    <input type="month" name="month" required value="{{ date('Y-m') }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 outline-none shadow-sm">
                </div>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full rounded-2xl bg-blue-600 px-6 py-4 text-sm font-black text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 active:scale-[0.98]">
                    Cetak Laporan
                </button>
            </div>
        </form>
    </x-modal>

</x-layouts.dashboard-shell>
