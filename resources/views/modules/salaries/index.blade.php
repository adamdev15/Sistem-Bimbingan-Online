@php
    $openSalaryCreate = $errors->any() && old('form_context') === 'salary_create';
    $hasTutors = $tutors->isNotEmpty();
@endphp
<x-layouts.dashboard-shell title="Gaji tutor — eBimbel">
    <div x-data="{ salaryModalOpen: @json($openSalaryCreate) }" class="space-y-6">
        <x-module-page-header
            title="Gaji tutor"
            description="Kelola data Gaji tutor, dengan informasi rekap periode, nominal, dan status (pending → dibayar → diterima)."
        >
        </x-module-page-header>

        @if (session('status'))
            <p class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        @if (! $hasTutors)
            <p class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Belum ada tutor terdaftar. Tambahkan tutor terlebih dahulu untuk mencatat gaji.</p>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 p-5">

            {{-- FILTER + ACTION --}}
            <div class="flex flex-wrap items-end gap-3 p-4 border-b border-slate-100 mb-6">

                <form method="GET" class="flex flex-wrap items-end gap-3 flex-1">
                    <div class="flex-1 min-w-[150px]">
                        <label for="status" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                        <select name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            <option value="">Semua status</option>
                            <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                            <option value="dibayar" @selected(($filters['status'] ?? '') === 'dibayar')>Dibayar</option>
                            <option value="diterima" @selected(($filters['status'] ?? '') === 'diterima')>Diterima</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label for="tutor_id" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tutor</label>
                        <select name="tutor_id" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            <option value="">Semua tutor</option>
                            @foreach ($tutors as $t)
                                <option value="{{ $t->id }}" @selected(($filters['tutor_id'] ?? '') == (string) $t->id)>{{ $t->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Filter</button>
                    <a href="{{ route('salaries.index') }}" class="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                </form>

                {{-- BUTTON RIGHT --}}
                <div class="flex flex-wrap items-center gap-2 ml-auto">
                    <button type="button" @click="$dispatch('open-export-salary-modal')" class="inline-flex items-center gap-2 rounded-xl border border-rose-200 bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak PDF
                    </button>
                    <button
                        type="button"
                        @click="salaryModalOpen = true"
                        @if (! $hasTutors) disabled @endif
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 hover:bg-blue-700 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Gaji Tutor
                    </button>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3.5">Periode</th>
                            <th class="px-4 py-3.5">Tutor</th>
                            <th class="px-4 py-3.5">Total Kehadiran</th>
                            <th class="px-4 py-3.5">Gaji</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Dicatat oleh</th>
                            <th class="px-4 py-3.5">Ubah status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($salaries as $s)
                            <tr class="text-slate-700 transition hover:bg-slate-50/80">
                                <td class="px-4 py-3.5 font-medium">{{ $s->periode }}</td>
                                <td class="px-4 py-3.5">{{ optional($s->tutor)->nama }}</td>
                                <td class="px-4 py-3.5">{{ $s->total_kehadiran }} Sesi</td>
                                <td class="px-4 py-3.5">Rp {{ number_format((float) $s->total_gaji, 0, ',', '.') }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold @class([
                                        'bg-amber-100 text-amber-900' => $s->status === 'pending',
                                        'bg-blue-100 text-blue-900' => $s->status === 'dibayar',
                                        'bg-emerald-100 text-emerald-900' => $s->status === 'diterima',
                                    ])">{{ ucfirst($s->status) }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-slate-600">{{ optional($s->creator)->name ?? '—' }}</td>
                                <td class="px-4 py-3.5">
                                    <form method="POST" action="{{ route('salaries.update', $s) }}" class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="rounded-lg border border-slate-200 px-2 py-1.5 text-xs shadow-sm focus:border-blue-300 focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                                            <option value="pending" @selected($s->status === 'pending')>Pending</option>
                                            <option value="dibayar" @selected($s->status === 'dibayar')>Dibayar</option>
                                            <option value="diterima" @selected($s->status === 'diterima')>Diterima</option>
                                        </select>
                                        <button type="submit" class="rounded-lg bg-slate-800 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-slate-900">OK</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-slate-500">Belum ada data gaji.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">{{ $salaries->links() }}</div>
        </div>

        {{-- Modal: entri gaji baru --}}
        <div
            x-data="{ 
                tutorId: '{{ old('tutor_id', $tutors->first()->id ?? '') }}',
                periode: '{{ old('periode', date('Y-m')) }}',
                totalKehadiran: {{ old('total_kehadiran', 0) }},
                isLoading: false,
                async fetchAttendance() {
                    if (!this.tutorId || !this.periode) return;
                    this.isLoading = true;
                    try {
                        const res = await fetch(`{{ route('api.salaries.attendance-count') }}?tutor_id=${this.tutorId}&periode=${this.periode}`);
                        const data = await res.json();
                        this.totalKehadiran = data.count || 0;
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.isLoading = false;
                    }
                }
            }"
            x-show="salaryModalOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="salary-create-title"
        >
            <div
                @click.outside="salaryModalOpen = false"
                @keydown.escape.window="salaryModalOpen = false"
                class="max-h-[min(90vh,640px)] w-full max-w-lg overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 id="salary-create-title" class="text-lg font-bold tracking-tight text-slate-900">Entri gaji baru</h3>
                        <p class="mt-1 text-sm text-slate-500">Pilih tutor dan periode, kehadiran akan dihitung otomatis.</p>
                    </div>
                    <button type="button" @click="salaryModalOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('salaries.store') }}" class="mt-6 space-y-4">
                    @csrf
                    <input type="hidden" name="form_context" value="salary_create">
                    <div>
                        <label for="sal-tutor" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tutor</label>
                        <select id="sal-tutor" name="tutor_id" required x-model="tutorId" @change="fetchAttendance()" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            <option value="">-- Pilih Tutor --</option>
                            @foreach ($tutors as $t)
                                <option value="{{ $t->id }}">{{ $t->nama }}</option>
                            @endforeach
                        </select>
                        @error('tutor_id')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="sal-periode" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Periode</label>
                            <input id="sal-periode" name="periode" type="month" required x-model="periode" @change="fetchAttendance()" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            @error('periode')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="sal-hadir" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total kehadiran</label>
                            <div class="relative mt-1.5">
                                <input id="sal-hadir" name="total_kehadiran" type="number" min="0" required x-model="totalKehadiran" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15" :class="isLoading ? 'opacity-50' : ''">
                                <div x-show="isLoading" class="absolute right-3 top-2.5">
                                    <svg class="h-4 w-4 animate-spin text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                </div>
                            </div>
                            <p class="mt-1 text-[10px] text-slate-400">Dihitung otomatis dari presensi.</p>
                            @error('total_kehadiran')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="sal-gaji" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total gaji (Manual)</label>
                            <input id="sal-gaji" name="total_gaji" type="number" min="0" step="0.01" value="{{ old('form_context') === 'salary_create' ? old('total_gaji') : '' }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            @error('total_gaji')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label for="sal-catatan" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Catatan</label>
                        <textarea id="sal-catatan" name="catatan" rows="2" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15 shadow-sm">{{ old('catatan') }}</textarea>
                    </div>
                    <div>
                        <label for="sal-status" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status awal</label>
                        <select id="sal-status" name="status" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            <option value="pending" @selected(old('form_context') === 'salary_create' ? old('status', 'pending') === 'pending' : true)>Pending</option>
                            <option value="dibayar" @selected(old('form_context') === 'salary_create' && old('status') === 'dibayar')>Dibayar</option>
                            <option value="diterima" @selected(old('form_context') === 'salary_create' && old('status') === 'diterima')>Diterima</option>
                        </select>
                        @error('status')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                        <button type="button" @click="salaryModalOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: Pilih Periode Export --}}
        <div
            x-data="{ openExport: false, month: '{{ date('Y-m') }}' }"
            @open-export-salary-modal.window="openExport = true"
            x-show="openExport"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
            role="dialog"
        >
            <div @click.outside="openExport = false" class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-900/10">
                <div class="mb-5 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">Export Gaji Tutor</h3>
                    <button @click="openExport = false" class="text-slate-400 hover:text-slate-600">&times;</button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700">Pilih Periode (Bulan)</label>
                        <input type="month" x-model="month" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 outline-none shadow-sm">
                    </div>
                    <div class="pt-4 grid grid-cols-2 gap-3">
                        <a :href="`{{ route('salaries.export.pdf') }}?month=${month}`" target="_blank" class="flex items-center justify-center rounded-xl bg-rose-600 py-2.5 text-sm font-bold text-white hover:bg-rose-700 transition">
                            Cetak PDF
                        </a>
                        <a :href="`{{ route('salaries.export.excel') }}?month=${month}`" class="flex items-center justify-center rounded-xl bg-emerald-600 py-2.5 text-sm font-bold text-white hover:bg-emerald-700 transition">
                            Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard-shell>
