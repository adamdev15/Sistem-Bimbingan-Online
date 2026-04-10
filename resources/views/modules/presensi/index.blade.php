@php
    $isStaffRekap = auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']);
    $isSuperAdmin = auth()->user()->hasRole('super_admin');
    $presensiDesc = $isSiswa
        ? 'Riwayat kehadiran Anda per sesi belajar.'
        : 'Rekap kehadiran siswa per cabang. Gunakan menu Input Presensi untuk mengisi sesi.';
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $currentYear = date('Y');
@endphp
<x-layouts.dashboard-shell title="Absensi — eBimbel">
    <x-module-page-header title="Absensi & Kehadiran" :description="$presensiDesc">
    </x-module-page-header>

    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <div
        x-data="{
            cabangId: @js($filters['cabang_id'] ?? ''),
            tutors: @js($tutors),
            loading: false,
            printOpen: false,
            async fetchTutors() {
                if (!this.cabangId) {
                    this.tutors = [];
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch(`/api/cabang/${this.cabangId}/tutors`);
                    const data = await res.json();
                    this.tutors = data;
                } catch (e) {
                    console.error('Gagal memuat tutor');
                } finally {
                    this.loading = false;
                }
            }
        }"
        class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 p-5"
    >
        {{-- FILTER + ACTION --}}
        <div class="flex flex-wrap items-end gap-3 p-4 border-b border-slate-100 mb-6">

                <form method="GET" class="flex flex-wrap items-end gap-3 flex-1">
                    @if($isStaffRekap && $isSuperAdmin)
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cabang</label>
                            <select name="cabang_id" x-model="cabangId" @change="fetchTutors()" class="mt-1.5 px-6 rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300">
                                <option value="">Semua Cabang</option>
                                @foreach ($cabangs as $c)
                                    <option value="{{ $c->id }}" @selected(($filters['cabang_id'] ?? '') == (string) $c->id)>{{ $c->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if($isStaffRekap)
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Tutor</label>
                            <select name="tutor_id" class="mt-1.5 px-6 rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300">
                                <option value="">Semua Tutor</option>
                                <template x-for="t in tutors" :key="t.id">
                                    <option :value="t.id" :selected="t.id == @js($filters['tutor_id'] ?? '')" x-text="t.nama"></option>
                                </template>
                                @if(!$isSuperAdmin)
                                    @foreach ($tutors as $tutor)
                                        <option value="{{ $tutor->id }}" @selected(($filters['tutor_id'] ?? '') == (string) $tutor->id) >{{ $tutor->nama }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    @endif
                    <div class="flex-1 min-w-[200px]">
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
                            <th class="px-4 py-3">Tutor</th>
                            <th class="px-4 py-3">Siswa</th>
                            <th class="px-4 py-3">Waktu</th>
                        @else
                            <th class="px-4 py-3">Waktu</th>
                        @endif
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($presensis as $p)
                        @php $st = $p->status; @endphp
                        <tr class="text-slate-700 hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs">{{ optional($p->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ optional($p->materiLes)->nama_materi ?? '—' }}</td>
                            @if ($isStaffRekap)
                                <td class="px-4 py-3 text-xs text-slate-500">{{ optional($p->cabang)->nama_cabang ?? '—' }}</td>
                                <td class="px-4 py-3 font-medium text-slate-900">{{ optional($p->tutor)->nama ?? '—' }}</td>
                                <td class="px-4 py-3">{{ optional($p->siswa)->nama }}</td>
                                <td class="px-4 py-3 text-xs">{{ substr($p->jam_mulai, 0, 5) }} - {{ substr($p->jam_selesai, 0, 5) }}</td>
                            @else
                                <td class="px-4 py-3">{{ substr($p->jam_mulai, 0, 5) }} - {{ substr($p->jam_selesai, 0, 5) }}</td>
                            @endif
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold border @class([
                                    'bg-emerald-100 border-emerald-200 text-emerald-800' => $st === 'hadir',
                                    'bg-rose-100 border-rose-200 text-rose-800' => $st === 'alfa',
                                    'bg-blue-100 border-blue-200 text-blue-800' => $st === 'izin',
                                    'bg-yellow-100 border-yellow-200 text-yellow-800' => $st === 'sakit',
                                ])">{{ ucfirst($st) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isStaffRekap ? 7 : 4 }}" class="px-4 py-8 text-center text-slate-500">Belum ada data presensi.</td>
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
            {{-- Modal: Cetak Kartu (Outside scope for robustness) --}}
            <template x-teleport="body">
                <div x-show="printOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" role="dialog">
                    <div @click.outside="printOpen = false" class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-900/10 transition-all">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold text-slate-900">Cetak Kartu Absensi</h3>
                            <button @click="printOpen = false" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
                        </div>
                        <form action="{{ route('presensi.print-card') }}" method="GET" target="_blank" class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700">Pilih Siswa</label>
                                <select name="student_id" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 outline-none shadow-sm">
                                    <option value="">-- Pilih Siswa --</option>
                                    @php
                                        $adminCabangId = auth()->user()->hasRole('admin_cabang') ? \App\Models\Cabang::where('user_id', auth()->id())->value('id') : null;
                                        $siswaList = \App\Models\Siswa::with('cabang')->when($adminCabangId, fn($q) => $q->where('cabang_id', $adminCabangId))->orderBy('nama')->get();
                                    @endphp
                                    @foreach($siswaList as $s)
                                        <option value="{{ $s->id }}">{{ $s->nama }} ({{ $s->cabang->nama_cabang ?? '' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700">Bulan</label>
                                    <select name="bulan" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 outline-none shadow-sm">
                                        @foreach($months as $num => $name)
                                            <option value="{{ $num }}" @selected($num == date('n'))>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700">Tahun</label>
                                    <select name="tahun" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 outline-none shadow-sm">
                                        @for($y = (int)date('Y') - 2; $y <= (int)date('Y') + 1; $y++)
                                            <option value="{{ $y }}" @selected($y == (int)date('Y'))>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="pt-4 flex justify-end gap-3 text-sm font-semibold">
                                <button type="button" @click="printOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-slate-700 hover:bg-slate-50">Batal</button>
                                <button type="submit" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-white shadow-sm hover:bg-emerald-700 transition">Cetak</button>
                            </div>
                        </form>
                    </div>
                </div>
            </template>
        @endif
    </div>
</x-layouts.dashboard-shell>

