@php
    $openSalaryCreate = $errors->any() && old('form_context') === 'salary_create';
    $hasTutors = $tutors->isNotEmpty();
@endphp
<x-layouts.dashboard-shell title="Gaji tutor — eBimbel">
    <div x-data="{ salaryModalOpen: @json($openSalaryCreate) }" class="space-y-6">
        <x-module-page-header
            title="Gaji tutor"
            description="Rekap periode, nominal, status alur (pending → dibayar → diterima), dan pencatat entri (created_by)."
        >
            @if (auth()->user()->hasRole('super_admin'))
            <x-slot name="actions">
                <button
                    type="button"
                    @click="salaryModalOpen = true"
                    @if (! $hasTutors) disabled @endif
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Gaji Tutor
                </button>
            </x-slot>
            @endif
        </x-module-page-header>

        @if (session('status'))
            <p class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        @if (! $hasTutors)
            <p class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">Belum ada tutor terdaftar. Tambahkan tutor terlebih dahulu untuk mencatat gaji.</p>
        @endif

        <form method="GET" class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm ring-1 ring-slate-900/5">
            <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                <option value="">Status — semua</option>
                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                <option value="dibayar" @selected(($filters['status'] ?? '') === 'dibayar')>Dibayar</option>
                <option value="diterima" @selected(($filters['status'] ?? '') === 'diterima')>Diterima</option>
            </select>
            <select name="tutor_id" class="min-w-[200px] rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                <option value="">Tutor — semua</option>
                @foreach ($tutors as $t)
                    <option value="{{ $t->id }}" @selected(($filters['tutor_id'] ?? '') == (string) $t->id)>{{ $t->nama }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Terapkan</button>
            <a href="{{ route('salaries.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3.5">Periode</th>
                            <th class="px-4 py-3.5">Tutor</th>
                            <th class="px-4 py-3.5">Jam</th>
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
                                <td class="px-4 py-3.5">{{ $s->total_jam }}</td>
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
                        <p class="mt-1 text-sm text-slate-500">Isi periode, jam, dan nominal; status awal bisa disesuaikan.</p>
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
                        <select id="sal-tutor" name="tutor_id" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            @foreach ($tutors as $t)
                                <option value="{{ $t->id }}" @selected(old('form_context') === 'salary_create' && (string) old('tutor_id') === (string) $t->id)>{{ $t->nama }}</option>
                            @endforeach
                        </select>
                        @error('tutor_id')
                            <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="sal-periode" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Periode</label>
                            <input id="sal-periode" name="periode" value="{{ old('form_context') === 'salary_create' ? old('periode') : '' }}" required placeholder="2026-04" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            @error('periode')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="sal-jam" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total jam</label>
                            <input id="sal-jam" name="total_jam" type="number" min="0" value="{{ old('form_context') === 'salary_create' ? old('total_jam', 0) : 0 }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            @error('total_jam')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="sal-gaji" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total gaji</label>
                            <input id="sal-gaji" name="total_gaji" type="number" min="0" step="0.01" value="{{ old('form_context') === 'salary_create' ? old('total_gaji') : '' }}" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            @error('total_gaji')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
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
    </div>
</x-layouts.dashboard-shell>
