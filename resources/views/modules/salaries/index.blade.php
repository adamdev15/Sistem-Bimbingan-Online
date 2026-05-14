@php
    $openSalaryCreate = $errors->any() && old('form_context') === 'salary_create';
    $hasTutors = $tutors->isNotEmpty();
@endphp
<x-layouts.dashboard-shell title="Gaji tutor — Jarimatrik">
    <div x-data="salaryManager">
        <div class="space-y-6">
            <x-module-page-header
                title="Gaji tutor"
                description="Kelola data Gaji tutor secara dinamis sesuai kehadiran dan rincian item kehadiran."
            >
            </x-module-page-header>

            @if (session('status'))
                <p class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
            @endif

            @if (session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Peringatan',
                            text: @js(session('error')),
                            confirmButtonColor: '#3085d6',
                        });
                    });
                </script>
                <p class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</p>
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
                            Cetak Laporan
                        </button>
                        <button
                            type="button"
                            @click="resetForm(); salaryModalOpen = true"
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
                                <th class="px-4 py-3.5">Total Gaji</th>
                                <th class="px-4 py-3.5">Status</th>
                                <th class="px-4 py-3.5">Catatan</th>
                                <th class="px-4 py-3.5">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($salaries as $s)
                                <tr class="text-slate-700 transition hover:bg-slate-50/80">
                                    <td class="px-4 py-3.5">
                                        <div class="font-medium text-slate-900">{{ $s->periode }}</div>
                                        @if($s->start_date && $s->end_date)
                                            <div class="text-[11px] text-slate-500">{{ $s->start_date->format('d/m/Y') }} - {{ $s->end_date->format('d/m/Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">{{ optional($s->tutor)->nama }}</td>
                                    <td class="px-4 py-3.5 font-bold text-slate-900">
                                        Rp {{ number_format($s->total_gaji, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 ring-inset {{ 
                                            $s->status === 'dibayar' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 
                                            ($s->status === 'diterima' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : 
                                            'bg-amber-50 text-amber-700 ring-amber-600/20') 
                                        }}">
                                            {{ ucfirst($s->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-500 italic text-xs">{{ $s->catatan }}</td>
                                    <td class="px-4 py-3.5">
                                        <div class="flex items-center gap-2">
                                            {{-- Detail --}}
                                            <button type="button" @click="showDetail({{ json_encode($s->load(['tutor', 'items', 'creator'])) }})" class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition shadow-sm" title="Detail">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            </button>
                                            
                                            {{-- Edit --}}
                                            <button type="button" @click="editSalary({{ json_encode($s->load('items')) }})" class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition shadow-sm" title="Edit">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                            </button>

                                            {{-- Print Slip --}}
                                            <a href="{{ route('salaries.print-slip', $s) }}" target="_blank" class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition shadow-sm" title="Cetak Slip">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            </a>

                                            {{-- Delete --}}
                                            <form action="{{ route('salaries.destroy', $s) }}" method="POST" class="inline-flex delete-salary-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" onclick="confirmDeleteSalary(this)" class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition shadow-sm" title="Hapus">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">Belum ada data gaji.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">{{ $salaries->links() }}</div>
            </div>

            {{-- Modal: Form Gaji (Create/Edit) --}}
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
                    class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="salary-create-title" class="text-lg font-bold tracking-tight text-slate-900" x-text="mode === 'create' ? 'Entri gaji baru' : 'Edit data gaji'"></h3>
                            <p class="mt-1 text-sm text-slate-500" x-text="mode === 'create' ? 'Input rincian honor tutor secara dinamis sesuai kehadiran.' : 'Perbarui rincian honor tutor yang sudah tercatat.'"></p>
                        </div>
                        <button type="button" @click="salaryModalOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" :action="mode === 'create' ? '{{ route('salaries.store') }}' : `{{ url('gaji-tutor') }}/${selectedSalaryId}`" class="mt-6 space-y-5">
                        @csrf
                        <template x-if="mode === 'edit'">
                            <input type="hidden" name="_method" value="PATCH">
                        </template>
                        <input type="hidden" name="form_context" value="salary_create">
                        
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pilih Tutor</label>
                                <input type="hidden" name="tutor_id" :value="tutorId">
                                
                                <div class="relative mt-1.5">
                                    <button type="button" 
                                            @click="showTutorList = !showTutorList"
                                            class="w-full flex items-center justify-between rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15 bg-white text-left transition-all">
                                        <span x-text="selectedTutorName" :class="tutorId ? 'text-slate-900' : 'text-slate-400'">Pilih Tutor</span>
                                        <svg class="h-4 w-4 text-slate-400 transition-transform" :class="showTutorList ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                                    </button>

                                    <div x-show="showTutorList" 
                                         @click.outside="showTutorList = false"
                                         x-transition
                                         class="absolute z-[60] mt-2 w-full rounded-2xl border border-slate-200 bg-white p-2 shadow-2xl ring-1 ring-black/5 animate-in fade-in zoom-in-95 duration-200">
                                        <div class="mb-2 p-1">
                                            <div class="relative">
                                                <input type="text" x-model="tutorSearch" placeholder="Cari nama tutor..." class="w-full rounded-xl border border-slate-100 bg-slate-50 pl-9 pr-4 py-2 text-xs font-semibold focus:border-blue-500 focus:ring-4 focus:ring-blue-500/5 outline-none transition-all">
                                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            </div>
                                        </div>
                                        <div class="max-h-60 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-200">
                                            <template x-for="t in filteredTutors" :key="t.id">
                                                <button type="button" 
                                                        @click="tutorId = t.id; selectedTutorName = t.nama; showTutorList = false; tutorSearch = ''"
                                                        class="w-full px-4 py-3 text-left rounded-xl hover:bg-blue-50 transition group"
                                                        :class="tutorId == t.id ? 'bg-blue-600 text-white' : 'text-slate-700'">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex flex-col">
                                                            <span x-text="t.nama" class="text-sm font-bold"></span>
                                                            <span class="text-[10px] opacity-70 uppercase tracking-tight font-medium" x-text="t.cabang?.nama_cabang || 'Cabang tidak diketahui'"></span>
                                                        </div>
                                                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-md bg-slate-100 group-hover:bg-white/20 text-slate-500 group-hover:text-inherit" x-text="t.jenis_tutor || 'Parttime'"></span>
                                                    </div>
                                                </button>
                                            </template>
                                            <template x-if="filteredTutors.length === 0">
                                                <div class="py-10 text-center">
                                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tutor tidak ditemukan</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="sal-start" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dari Tanggal</label>
                                <input id="sal-start" name="start_date" type="date" required x-model="startDate" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label for="sal-end" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sampai Tanggal</label>
                                <input id="sal-end" name="end_date" type="date" required x-model="endDate" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="sal-periode" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Label Periode</label>
                                <input id="sal-periode" name="periode" type="text" required x-model="periode" placeholder="April 2024" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                        </div>

                        {{-- ITEMS --}}
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Rincian Item</p>
                                <button type="button" @click="addItem()" class="text-[10px] font-bold text-blue-600 hover:text-blue-700 uppercase">+ Tambah Item</button>
                            </div>
                            <div class="overflow-hidden rounded-xl border border-slate-200">
                                <table class="min-w-full divide-y divide-slate-200 text-xs">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-bold text-slate-600">Status Kehadiran</th>
                                            <th class="px-3 py-2 text-left font-bold text-slate-600 w-16 text-center">Jumlah</th>
                                            <th class="px-3 py-2 text-left font-bold text-slate-600 w-28 text-right">Tarif Persesi</th>
                                            <th class="px-3 py-2 text-left font-bold text-slate-600 w-28 text-right">Subtotal</th>
                                            <th class="px-3 py-2 w-8"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        <template x-for="(item, index) in items" :key="index">
                                            <tr>
                                                <td class="p-2">
                                                    <input type="text" :name="`items[${index}][nama_item]`" x-model="item.nama_item" placeholder="Contoh: Sesi Full" class="w-full border-none p-1 focus:ring-0 text-xs">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" step="0.1" :name="`items[${index}][qty]`" x-model="item.qty" @input="calculateSubtotal(index)" class="w-full border-none p-1 focus:ring-0 text-xs text-center">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" :name="`items[${index}][tarif]`" x-model="item.tarif" @input="calculateSubtotal(index)" class="w-full border-none p-1 focus:ring-0 text-xs text-right">
                                                </td>
                                                <td class="p-2">
                                                    <input type="number" :name="`items[${index}][subtotal]`" x-model="item.subtotal" readonly class="w-full border-none p-1 focus:ring-0 text-xs font-bold text-slate-900 text-right">
                                                </td>
                                                <td class="p-2 text-center">
                                                    <button type="button" @click="removeItem(index)" class="text-rose-500 hover:text-rose-700">
                                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="items.length === 0">
                                            <tr>
                                                <td colspan="5" class="p-4 text-center text-slate-400 italic">Belum ada item, klik 'Tambah Item' atau pilih tutor & tanggal.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                            <div>
                                <label for="sal-bonus" class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Bonus (Rp)</label>
                                <input id="sal-bonus" name="bonus" type="number" x-model="bonus" @input="calculateTotal()" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs shadow-sm outline-none focus:border-blue-300">
                            </div>
                            <div>
                                <label for="sal-lain" class="text-[10px] font-bold uppercase tracking-wide text-slate-500">Lain-lainnya (Rp)</label>
                                <input id="sal-lain" name="lain_lainnya" type="number" x-model="lainLainnya" @input="calculateTotal()" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-xs shadow-sm outline-none focus:border-blue-300">
                            </div>
                            <div class="sm:col-span-2 pt-2">
                                <label class="text-xs font-bold text-blue-600 uppercase">Total Gaji Akhir</label>
                                <div class="mt-1 text-2xl font-black text-slate-900">
                                    Rp <span x-text="new Intl.NumberFormat('id-ID').format(totalGaji || 0)"></span>
                                    <input type="hidden" name="total_gaji" :value="totalGaji">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="sal-status" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                            <select id="sal-status" name="status" required x-model="status" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="pending">Pending</option>
                                <option value="dibayar">Dibayar</option>
                                <option value="diterima">Diterima</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="sal-catatan-form" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Catatan (Opsional)</label>
                            <textarea id="sal-catatan-form" name="catatan" x-model="catatan" placeholder="Misal: Bonus lembur atau potongan keterlambatan" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15 min-h-[80px]"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="salaryModalOpen = false" class="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">Batal</button>
                            <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 hover:bg-blue-700 transition-all" x-text="mode === 'create' ? 'Simpan Data' : 'Perbarui Gaji'"></button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal: Detail Gaji --}}
            <div
                x-show="detailModalOpen"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]"
            >
                <div
                    @click.outside="detailModalOpen = false"
                    class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl"
                >
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6">
                        <h3 class="text-lg font-bold text-slate-900">Rincian Gaji Tutor</h3>
                        <button @click="detailModalOpen = false" class="text-slate-400 hover:text-slate-600"><svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
                    </div>
                    
                    <template x-if="detailData">
                        <div class="space-y-6">
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50 p-5 rounded-2xl border border-slate-100">
                                <div class="col-span-2">
                                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Tutor</p>
                                    <p class="font-bold text-slate-900" x-text="detailData.tutor?.nama"></p>
                                    <p class="text-[10px] font-bold text-blue-600 uppercase" x-text="detailData.tutor?.jenis_tutor || 'Parttime'"></p>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Periode</p>
                                    <p class="font-bold text-slate-900" x-text="detailData.periode"></p>
                                    <p class="text-[10px] text-slate-500" x-text="detailData.start_date.split('T')[0].split('-').reverse().join('/') + ' - ' + detailData.end_date.split('T')[0].split('-').reverse().join('/')"></p>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-widest mb-1">Status</p>
                                    <span class="inline-flex rounded-lg px-2 py-0.5 text-[10px] font-black uppercase tracking-wider ring-1 ring-inset" 
                                          :class="detailData.status === 'dibayar' ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 
                                                  (detailData.status === 'diterima' ? 'bg-blue-50 text-blue-700 ring-blue-600/20' : 
                                                  'bg-amber-50 text-amber-700 ring-amber-600/20')">
                                        <span x-text="detailData.status"></span>
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between px-1">
                                <div class="flex items-center gap-2">
                                    <div class="h-6 w-1 rounded-full bg-blue-600"></div>
                                    <h4 class="text-sm font-bold text-slate-800 uppercase tracking-tight">Rincian Kehadiran</h4>
                                </div>
                                <div class="text-right flex flex-col items-end">
                                    <div class="flex items-center gap-1.5 mb-0.5">
                                        <svg class="h-3 w-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <p class="text-[10px] font-bold text-slate-400 italic tracking-widest">Dibuat Oleh</p>
                                    </div>
                                    <p class="text-xs font-black text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg" x-text="detailData.creator?.name || 'Sistem'"></p>
                                </div>
                            </div>

                            <div>
                                <table class="w-full text-sm">
                                    <thead class="text-left text-slate-500 border-b border-slate-100">
                                        <tr>
                                            <th class="pb-2 font-semibold">Item</th>
                                            <th class="pb-2 font-semibold text-center">Qty</th>
                                            <th class="pb-2 font-semibold text-right">Tarif</th>
                                            <th class="pb-2 font-semibold text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        <template x-for="item in detailData.items" :key="item.id">
                                            <tr>
                                                <td class="py-2 text-slate-700" x-text="item.nama_item"></td>
                                                <td class="py-2 text-center text-slate-600" x-text="item.qty"></td>
                                                <td class="py-2 text-right text-slate-600" x-text="new Intl.NumberFormat('id-ID').format(item.tarif || 0)"></td>
                                                <td class="py-2 text-right font-bold text-slate-900" x-text="new Intl.NumberFormat('id-ID').format(item.subtotal || 0)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                    <tfoot class="border-t border-slate-200">
                                        <template x-if="detailData.bonus > 0">
                                            <tr>
                                                <td colspan="3" class="py-2 text-right text-slate-500">Bonus</td>
                                                <td class="py-2 text-right font-bold text-emerald-600" x-text="new Intl.NumberFormat('id-ID').format(detailData.bonus || 0)"></td>
                                            </tr>
                                        </template>
                                        <template x-if="detailData.lain_lainnya > 0">
                                            <tr>
                                                <td colspan="3" class="py-2 text-right text-slate-500">Lainnya</td>
                                                <td class="py-2 text-right font-bold text-blue-600" x-text="new Intl.NumberFormat('id-ID').format(detailData.lain_lainnya || 0)"></td>
                                            </tr>
                                        </template>
                                        <tr class="bg-slate-900 text-white rounded-lg">
                                            <td colspan="3" class="p-3 text-right font-bold">Total Gaji Diterima</td>
                                            <td class="p-3 text-right font-black text-lg" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(detailData.total_gaji || 0)"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div x-show="detailData.catatan" class="p-4 rounded-xl bg-blue-50 border border-blue-100">
                                <p class="text-[10px] font-black uppercase text-blue-600 mb-1">Catatan</p>
                                <p class="text-sm text-blue-900" x-text="detailData.catatan"></p>
                            </div>
                        </div>
                    </template>
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
    </div>

    {{-- SweetAlert2 for profesional look --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('salaryManager', () => ({
                salaryModalOpen: @json($openSalaryCreate),
                detailModalOpen: false,
                mode: 'create',
                selectedSalaryId: null,
                tutorId: '{{ old('tutor_id', '') }}',
                selectedTutorName: 'Pilih Tutor',
                showTutorList: false,
                tutorSearch: '',
                allTutors: @json($tutors),
                startDate: '{{ date('Y-m-01') }}',
                endDate: '{{ date('Y-m-t') }}',
                periode: '{{ date('F Y') }}',
                status: 'pending',
                catatan: '',
                items: [],
                bonus: 0,
                lainLainnya: 0,
                totalGaji: 0,
                isLoading: false,
                detailData: null,

                get filteredTutors() {
                    if (!this.tutorSearch) return this.allTutors;
                    return this.allTutors.filter(t => t.nama.toLowerCase().includes(this.tutorSearch.toLowerCase()));
                },

                init() {
                    if (this.tutorId) {
                        const t = this.allTutors.find(x => x.id == this.tutorId);
                        if (t) this.selectedTutorName = t.nama;
                    }
                    this.$watch('tutorId', () => this.fetchAttendance());
                    this.$watch('startDate', () => this.fetchAttendance());
                    this.$watch('endDate', () => this.fetchAttendance());
                },

                addItem() {
                    this.items.push({ nama_item: '', qty: 1, tarif: 0, subtotal: 0, keterangan: '' });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                    this.calculateTotal();
                },
                calculateSubtotal(index) {
                    let item = this.items[index];
                    item.subtotal = (parseFloat(item.qty) || 0) * (parseFloat(item.tarif) || 0);
                    this.calculateTotal();
                },
                calculateTotal() {
                    let itemsTotal = this.items.reduce((sum, item) => sum + (parseFloat(item.subtotal) || 0), 0);
                    this.totalGaji = itemsTotal + (parseFloat(this.bonus) || 0) + (parseFloat(this.lainLainnya) || 0);
                },
                async fetchAttendance() {
                    if (this.mode !== 'create') return;
                    if (!this.tutorId || !this.startDate || !this.endDate) return;
                    this.isLoading = true;
                    
                    const d = new Date(this.startDate);
                    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                    this.periode = `${monthNames[d.getMonth()]} ${d.getFullYear()}`;

                    try {
                        const res = await fetch(`{{ route('api.salaries.attendance-count') }}?tutor_id=${this.tutorId}&start_date=${this.startDate}&end_date=${this.endDate}`);
                        const data = await res.json();
                        this.items = data.items.map(i => ({
                            nama_item: i.nama_item,
                            qty: i.qty,
                            tarif: 0,
                            subtotal: 0,
                            keterangan: ''
                        }));
                        this.calculateTotal();
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.isLoading = false;
                    }
                },
                editSalary(salary) {
                    this.mode = 'edit';
                    this.selectedSalaryId = salary.id;
                    this.tutorId = salary.tutor_id;
                    const t = this.allTutors.find(x => x.id == this.tutorId);
                    this.selectedTutorName = t ? t.nama : 'Pilih Tutor';
                    this.startDate = salary.start_date.split('T')[0];
                    this.endDate = salary.end_date.split('T')[0];
                    this.periode = salary.periode;
                    this.status = salary.status;
                    this.catatan = salary.catatan || '';
                    this.bonus = parseFloat(salary.bonus) || 0;
                    this.lainLainnya = parseFloat(salary.lain_lainnya) || 0;
                    this.items = salary.items.map(i => ({
                        nama_item: i.nama_item,
                        qty: i.qty,
                        tarif: i.tarif,
                        subtotal: i.subtotal,
                        keterangan: i.keterangan || ''
                    }));
                    this.calculateTotal();
                    this.salaryModalOpen = true;
                },
                showDetail(salary) {
                    this.detailData = salary;
                    this.detailModalOpen = true;
                },
                resetForm() {
                    this.mode = 'create';
                    this.selectedSalaryId = null;
                    this.tutorId = '';
                    this.selectedTutorName = 'Pilih Tutor';
                    this.startDate = '{{ date('Y-m-01') }}';
                    this.endDate = '{{ date('Y-m-t') }}';
                    this.periode = '{{ date('F Y') }}';
                    this.status = 'pending';
                    this.catatan = '';
                    this.items = [];
                    this.bonus = 0;
                    this.lainLainnya = 0;
                    this.totalGaji = 0;
                }
            }))
        })

        function confirmDeleteSalary(button) {
            const form = button.closest('form');
            Swal.fire({
                title: 'Hapus data gaji?',
                text: "Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</x-layouts.dashboard-shell>
