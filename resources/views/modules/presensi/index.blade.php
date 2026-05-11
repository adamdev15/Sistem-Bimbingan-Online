@php
    $isStaffRekap = auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']);
    $isSuperAdmin = auth()->user()->hasRole('super_admin');
    $presensiDesc = $isSiswa
        ? 'Riwayat kehadiran Anda per sesi belajar.'
        : 'Kelola data Absensi kehadiran siswa per sesi belajar. Dengan informasi rekap kartu Absensi siswa';
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $currentYear = date('Y');
@endphp
<x-layouts.dashboard-shell title="Absensi Siswa — Bimbel Jarimatrik">
    <x-module-page-header title="Absensi Siswa" :description="$presensiDesc">
    </x-module-page-header>

    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif    
    
    <div
        x-data="{
            cabangId: @js($filters['cabang_id'] ?? ''),
            loading: false,
            printOpen: false,
            printCabangId: '{{ auth()->user()->hasRole('admin_cabang') ? \App\Models\Cabang::where('user_id', auth()->id())->value('id') : '' }}',
            printStudents: [],
            printStudentId: '',
            printStudentSearch: '',
            showStudentList: false,
            editOpen: false,
            editData: {
                id: null,
                status: 'hadir',
                catatan: '',
                siswa_nama: '',
                tanggal: ''
            },
            doEdit(p) {
                this.editData = {
                    id: p.id,
                    status: p.status,
                    catatan: p.catatan || '',
                    siswa_nama: p.siswa ? p.siswa.nama : '—',
                    tanggal: p.tanggal_fmt || p.tanggal
                };
                this.editOpen = true;
            },
            get selectedStudentName() {
                const s = this.printStudents.find(x => x.id == this.printStudentId);
                if (!s) return 'Pilih Siswa';
                return `${s.nama} - ${this.formatIndoDate(s.created_at)}`;
            },
            get filteredStudents() {
                if (!this.printStudentSearch) return this.printStudents;
                return this.printStudents.filter(s => s.nama.toLowerCase().includes(this.printStudentSearch.toLowerCase()));
            },
            formatIndoDate(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
            },
            async fetchPrintStudents() {
                if (!this.printCabangId) {
                    this.printStudents = [];
                    this.printStudentId = '';
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch(`/api/cabang/${this.printCabangId}/students`);
                    this.printStudents = await res.json();
                } catch (e) {
                    console.error('Gagal memuat siswa');
                } finally {
                    this.loading = false;
                }
            }
        }"
        x-init="if(printCabangId) fetchPrintStudents()"
        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 p-5"
    >
        {{-- FILTER + ACTION --}}
        <div class="flex flex-wrap items-end gap-3 p-4 border-b border-slate-100 mb-6">
            <form method="GET" class="flex flex-wrap items-end gap-3 flex-1">
                @if($isStaffRekap && $isSuperAdmin)
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cabang</label>
                        <select name="cabang_id" x-model="cabangId" class="mt-1.5 px-6 rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300">
                            <option value="">Semua Cabang</option>
                            @foreach ($cabangs as $c)
                                <option value="{{ $c->id }}" @selected(($filters['cabang_id'] ?? '') == (string) $c->id)>{{ $c->nama_cabang }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if($isStaffRekap)
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Pilih Materi Les</label>
                        <select name="materi_les_id" class="mt-1.5 px-6 rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300">
                            <option value="">Semua Materi</option>
                            @foreach ($materis as $m)
                                <option value="{{ $m->id }}" @selected(($filters['materi_les_id'] ?? '') == (string) $m->id)>{{ $m->nama_materi }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="flex-1 min-w-[100px]">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Pilih Status</label>
                    <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300">
                        <option value="">Semua Status</option>
                        <option value="hadir" @selected(($filters['status'] ?? '') == 'hadir')>Hadir</option>
                        <option value="izin" @selected(($filters['status'] ?? '') == 'izin')>Izin</option>
                        <option value="alfa" @selected(($filters['status'] ?? '') == 'alfa')>Alfa</option>
                        <option value="sakit" @selected(($filters['status'] ?? '') == 'sakit')>Sakit</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[100px]">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Pilih Periode</label>
                    <input name="month" type="month" value="{{ $filters['month'] ?? '' }}" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300">
                </div>
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Filter</button>
            </form>

            {{-- BUTTON RIGHT --}}
            <div class="flex flex-wrap items-center gap-2 ml-auto">
                @if ($isStaffRekap)
                    <button @click="printOpen = true" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak Kartu
                    </button>

                    <a href="{{ route('presensi.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 hover:bg-blue-700 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Absensi
                    </a>
                @endif
            </div>
        </div>

        {{-- TABLE --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Materi Les</th>
                        @if ($isStaffRekap)
                            <th class="px-4 py-3">Cabang</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Waktu</th>
                        @else
                            <th class="px-4 py-3">Waktu</th>
                        @endif
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Catatan</th>
                        @if ($isStaffRekap)
                            <th class="px-4 py-3 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($presensis as $p)
                        @php $st = $p->status; @endphp
                        <tr class="text-slate-700 hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-900">{{ optional($p->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ optional($p->materiLes)->nama_materi ?? '—' }}</td>
                            @if ($isStaffRekap)
                                <td class="px-4 py-3 text-xs font-bold text-slate-900">{{ optional($p->cabang)->nama_cabang ?? '—' }}</td>
                                <td class="px-4 py-3">{{ optional($p->siswa)->nama }}</td>
                                <td class="px-4 py-3 text-xs font-mono">{{ substr($p->jam_mulai, 0, 5) }} - {{ substr($p->jam_selesai, 0, 5) }}</td>
                            @else
                                <td class="px-4 py-3 text-xs font-mono">{{ substr($p->jam_mulai, 0, 5) }} - {{ substr($p->jam_selesai, 0, 5) }}</td>
                            @endif
                            <td class="px-4 py-3">
                                @php
                                    $config = [
                                        'hadir' => ['color' => 'bg-emerald-50 text-emerald-700 border-emerald-100', 'dot' => 'bg-emerald-500'],
                                        'alfa'   => ['color' => 'bg-rose-50 text-rose-700 border-rose-100', 'dot' => 'bg-rose-500'],
                                        'izin'   => ['color' => 'bg-blue-50 text-blue-700 border-blue-100', 'dot' => 'bg-blue-500'],
                                        'sakit'  => ['color' => 'bg-amber-50 text-amber-700 border-amber-100', 'dot' => 'bg-amber-500'],
                                    ];
                                    $stCfg = $config[$st] ?? ['color' => 'bg-slate-50 text-slate-700 border-slate-100', 'dot' => 'bg-slate-500'];
                                @endphp
                                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-black uppercase tracking-wider border {{ $stCfg['color'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $stCfg['dot'] }}"></span>
                                    {{ $st }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $p->catatan ?? '—' }}</td>
                            @if ($isStaffRekap)
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="doEdit({{ json_encode($p) }})" class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 hover:bg-yellow-100 p-2 rounded-lg transition-colors" title="Edit">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>
                                        <form method="POST" action="{{ route('presensi.destroy', $p) }}" class="inline" onsubmit="event.preventDefault(); confirmDelete(this, 'Hapus absensi {{ $p->siswa?->nama }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors" title="Hapus">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isStaffRekap ? 8 : 4 }}" class="px-4 py-8 text-center text-slate-500">Belum ada data presensi siswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($presensis->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
                {{ $presensis->links() }}
            </div>
        @endif

        @if ($isStaffRekap)
            {{-- Modal: Cetak Kartu --}}
            <template x-teleport="body">
                <div x-show="printOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" role="dialog">
                    <div @click.outside="printOpen = false" class="w-full max-w-lg rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-900/10 transition-all">
                        <div class="mb-6 flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Cetak Kartu Absensi</h3>
                                <p class="text-sm text-slate-500 mt-1">Pilih siswa untuk mengunduh rekap bulanan.</p>
                            </div>
                            <button @click="printOpen = false" class="h-8 w-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-100 hover:text-rose-600 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <form action="{{ route('presensi.print-card') }}" method="GET" target="_blank" class="space-y-5">
                            @if($isSuperAdmin)
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Pilih Cabang</label>
                                    <select x-model="printCabangId" @change="fetchPrintStudents()" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm bg-slate-50/50">
                                        <option value="">-- Semua Cabang --</option>
                                        @foreach($cabangs as $c)
                                            <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="relative">
                                <label class="block text-sm font-bold text-slate-700 mb-1.5">Pilih Siswa</label>
                                <input type="hidden" name="student_id" x-model="printStudentId">
                                
                                <div class="relative">
                                    <button type="button" 
                                            @click="showStudentList = !showStudentList"
                                            class="w-full flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm bg-white text-left overflow-hidden">
                                        <span x-text="selectedStudentName" class="truncate">Pilih Siswa</span>
                                        <svg class="h-4 w-4 text-slate-400 transition-transform" :class="showStudentList ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                    </button>

                                    <div x-show="showStudentList" 
                                         @click.outside="showStudentList = false"
                                         x-transition
                                         class="absolute z-[80] mt-2 w-full rounded-2xl border border-slate-200 bg-white p-2 shadow-xl ring-1 ring-black/5">
                                        <div class="mb-2 p-1">
                                            <input type="text" x-model="printStudentSearch" placeholder="Cari siswa..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs focus:border-blue-500 outline-none">
                                        </div>
                                        <div class="max-h-52 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-200">
                                            <template x-for="s in filteredStudents" :key="s.id">
                                                <button type="button" 
                                                        @click="printStudentId = s.id; showStudentList = false; printStudentSearch = ''"
                                                        class="w-full px-3 py-2 text-left text-sm rounded-lg hover:bg-blue-50 hover:text-blue-700 transition flex items-center justify-between"
                                                        :class="printStudentId == s.id ? 'bg-blue-600 text-white' : 'text-slate-700'">
                                                    <span x-text="s.nama" class="font-medium"></span>
                                                    <span class="text-[10px] opacity-70" x-text="formatIndoDate(s.created_at)"></span>
                                                </button>
                                            </template>
                                            <template x-if="filteredStudents.length === 0">
                                                <p class="p-3 text-center text-xs text-slate-400">Siswa tidak ditemukan</p>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4" x-data="{
                                init() {
                                    this.$watch('printStudentId', (id) => {
                                        const s = this.printStudents.find(x => x.id == id);
                                        if (s && s.created_at) {
                                            const regDate = new Date(s.created_at);
                                            const now = new Date();
                                            const day = String(regDate.getDate()).padStart(2, '0');
                                            const month = String(now.getMonth() + 1).padStart(2, '0');
                                            const year = now.getFullYear();
                                            
                                            // Pre-fill "Periode Dari" with student's registration day but current month/year
                                            const startDateInput = document.getElementById('print-start-date');
                                            if (startDateInput) startDateInput.value = `${year}-${month}-${day}`;
                                        }
                                    });
                                }
                            }">
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Periode Dari</label>
                                    <input type="date" id="print-start-date" name="start_date" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm bg-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-1.5">Periode Sampai</label>
                                    <input type="date" name="end_date" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm bg-white">
                                </div>
                            </div>
                            <div class="pt-6 flex flex-col gap-3">
                                <button type="submit" :disabled="!printStudentId" class="w-full flex items-center justify-center gap-2 rounded-2xl bg-emerald-600 px-6 py-4 text-sm font-bold text-white shadow-lg transition-all hover:bg-emerald-700 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    Cetak Kartu Absensi
                                </button>
                                <button type="button" @click="printOpen = false" class="w-full py-2 text-sm font-semibold text-slate-500 hover:text-slate-800 transition">Batalkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        @endif

        {{-- Modal: Edit Absensi --}}
        <template x-teleport="body">
            <div x-show="editOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" role="dialog">
                <div @click.outside="editOpen = false" class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl ring-1 ring-slate-900/10 transition-all">
                    <div class="mb-6 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">Edit Absensi</h3>
                            <p class="text-sm text-slate-500 mt-1" x-text="`${editData.siswa_nama} — ${editData.tanggal}`"></p>
                        </div>
                        <button @click="editOpen = false" class="h-8 w-8 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-100 hover:text-rose-600 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="`{{ url('/presensi') }}/${editData.id}`" method="POST" class="space-y-5">
                        @csrf
                        @method('PATCH')
                        
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Status Kehadiran</label>
                            <div class="grid grid-cols-2 gap-2">
                                <template x-for="st in ['hadir', 'izin', 'alfa', 'sakit']">
                                    <button type="button" 
                                            @click="editData.status = st"
                                            :class="editData.status === st ? 'bg-blue-600 text-white border-blue-600 shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                            class="rounded-xl border px-4 py-2.5 text-xs font-black uppercase tracking-widest transition-all">
                                        <span x-text="st"></span>
                                    </button>
                                </template>
                            </div>
                            <input type="hidden" name="status" x-model="editData.status">
                        </div>

                        <div>
                            <label for="edit-catatan" class="block text-sm font-bold text-slate-700 mb-2">Catatan</label>
                            <textarea id="edit-catatan" name="catatan" x-model="editData.catatan" rows="3" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none shadow-sm bg-slate-50/50" placeholder="Keterangan opsional..."></textarea>
                        </div>

                        <div class="pt-6 flex flex-col gap-3">
                            <button type="submit" class="w-full flex items-center justify-center gap-2 rounded-2xl bg-blue-600 px-6 py-4 text-sm font-bold text-white shadow-lg transition-all hover:bg-blue-700 active:scale-[0.98]">
                                Simpan Perubahan
                            </button>
                            <button type="button" @click="editOpen = false" class="w-full py-2 text-sm font-semibold text-slate-500 hover:text-slate-800 transition">Batalkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-layouts.dashboard-shell>
