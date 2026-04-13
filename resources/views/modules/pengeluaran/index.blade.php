<x-layouts.dashboard-shell title="Pengeluaran — Bimbel Jarimatrik">
    <x-module-page-header title="Pengeluaran Operasional" description="Pusat Kelola data biaya operasional cabang dengan informasi rekap periode.">
    </x-module-page-header>

    @if (session('status'))
        <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif

    <div x-data="{ 
        isOpen: false, 
        isEdit: false, 
        expense: { id: '', tanggal: '{{ date('Y-m-d') }}', kategori_id: '', nominal: '', keterangan: '', cabang_id: '{{ $cabangId }}' },
        init() {
            window.addEventListener('open-expense-modal', () => {
                this.isEdit = false;
                this.expense = { id: '', tanggal: '{{ date('Y-m-d') }}', kategori_id: '', nominal: '', keterangan: '', cabang_id: '{{ $cabangId }}' };
                this.isOpen = true;
            });
            window.addEventListener('edit-expense', (e) => {
                this.isEdit = true;
                this.expense = e.detail;
                this.isOpen = true;
            });
        }
    }">
        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 p-5">

            {{-- FILTER + ACTION --}}
            <div x-data="{ printOpen: false }" class="flex flex-wrap items-end gap-3 p-4 border-b border-slate-100 mb-6">

                <form method="GET" class="flex flex-wrap items-end gap-3 flex-1">
                    @if (!$cabangId)
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cabang</label>
                            <select name="cabang_id" border-slate-200 class="mt-1.5 rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                <option value="">Semua Cabang</option>
                                @foreach ($cabangs as $c)
                                    <option value="{{ $c->id }}" @selected(($filters['cabang_id'] ?? '') == (string) $c->id)>{{ $c->nama_cabang }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Kategori</label>
                        <select name="kategori_id" class="mt-1.5 rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            <option value="">Semua Kategori</option>
                            @foreach ($kategoris as $k)
                                <option value="{{ $k->id }}" @selected(($filters['kategori_id'] ?? '') == (string) $k->id)>{{ $k->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Pilih Periode</label>
                        <input name="month" type="month" value="{{ $filters['month'] ?? '' }}" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                    </div>
                    <button type="submit" class="rounded-xl bg-slate-900 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">Filter</button>
                </form>

                {{-- BUTTON RIGHT --}}
                <div class="flex flex-wrap items-center gap-2 ml-auto">
                    <button @click="printOpen = true" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak Laporan
                    </button>
                    <button @click="$dispatch('open-expense-modal')" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 hover:bg-blue-700 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Pengeluaran
                    </button>
                    
                    {{-- Modal: Cetak Pengeluaran (Teleported) --}}
                    <template x-teleport="body">
                        <div x-show="printOpen" x-cloak class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm" role="dialog">
                            <div @click.outside="printOpen = false" class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl ring-1 ring-slate-900/10 transition-all">
                                <div class="mb-5 flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-slate-900">Cetak Laporan Operasional</h3>
                                    <button @click="printOpen = false" class="text-slate-400 hover:text-slate-600">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <form action="{{ route('pengeluaran.print') }}" method="GET" target="_blank" class="space-y-4">
                                    @if(!$cabangId)
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700">Pilih Cabang</label>
                                        <select name="cabang_id" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 outline-none shadow-sm">
                                            <option value="">-- Pilih Cabang --</option>
                                            @foreach($cabangs as $c)
                                                <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @else
                                        <input type="hidden" name="cabang_id" value="{{ $cabangId }}">
                                    @endif
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700">Pilih Periode (Bulan)</label>
                                        <input type="month" name="month" required value="{{ date('Y-m') }}" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-blue-500 outline-none shadow-sm">
                                    </div>
                                    <div class="pt-4 flex justify-end gap-3 text-sm font-semibold">
                                        <button type="button" @click="printOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-slate-700 hover:bg-slate-50">Batal</button>
                                        <button type="submit" class="rounded-xl bg-emerald-600 px-6 py-2.5 text-white shadow-sm hover:bg-emerald-700 transition">Lihat Laporan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Kategori</th>
                            @if (!$cabangId)
                                <th class="px-4 py-3">Cabang</th>
                            @endif
                            <th class="px-4 py-3">Keterangan</th>
                            <th class="px-4 py-3">Nominal</th>
                            <th class="px-4 py-3">Created By</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($pengeluarans as $p)
                            <tr class="text-slate-700 hover:bg-slate-50">
                                <td class="px-4 py-4 font-mono text-xs">{{ optional($p->tanggal)->format('d/m/Y') }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800">
                                        {{ $p->kategori->nama_kategori ?? 'Lain-lain' }}
                                    </span>
                                </td>
                                @if (!$cabangId)
                                    <td class="px-4 py-4 text-xs">{{ $p->cabang->nama_cabang ?? '—' }}</td>
                                @endif
                                <td class="px-4 py-4">{{ $p->keterangan }}</td>
                                <td class="px-4 py-4 font-bold">Rp {{ number_format((float) $p->nominal, 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-xs">{{ $p->creator->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button @click="$dispatch('edit-expense', { 
                                            id: '{{ $p->id }}', 
                                            tanggal: '{{ optional($p->tanggal)->format('Y-m-d') }}', 
                                            kategori_id: '{{ $p->kategori_id }}', 
                                            nominal: '{{ (int)$p->nominal }}', 
                                            keterangan: '{{ addslashes($p->keterangan) }}', 
                                            cabang_id: '{{ $p->cabang_id }}' 
                                        })" class="rounded-lg p-2 bg-amber-50 text-amber-600 hover:bg-amber-50" title="Edit">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </button>
                                        <form method="POST" action="{{ route('pengeluaran.destroy', $p->id) }}" onsubmit="return confirm('Hapus pengeluaran ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rounded-lg p-2 bg-rose-50 text-rose-600 hover:bg-rose-50" title="Hapus">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ !$cabangId ? 6 : 5 }}" class="px-4 py-8 text-center text-slate-500">Belum ada data pengeluaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($pengeluarans->hasPages())
                <div class="px-4 py-3 border-t border-slate-100 bg-slate-50">
                    {{ $pengeluarans->links() }}
                </div>
            @endif
        </div>

        <!-- Modal CRUD -->
        <template x-teleport="body">
            <div x-show="isOpen" 
                 class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-sm"
                 x-cloak
                 x-transition.opacity>
                
                <div @click.away="isOpen = false" 
                     class="w-full max-w-lg bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden"
                     x-transition.scale>
                    
                    <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
                        <h3 class="text-lg font-bold text-slate-800" x-text="isEdit ? 'Ubah Pengeluaran' : 'Tambah Pengeluaran Baru'"></h3>
                        <button @click="isOpen = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form :action="isEdit ? `{{ url('pengeluaran') }}/${expense.id}` : '{{ route('pengeluaran.store') }}'" method="POST">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Tanggal</label>
                                    <input type="date" name="tanggal" required x-model="expense.tanggal" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500 shadow-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Kategori</label>
                                    <select name="kategori_id" required x-model="expense.kategori_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500 shadow-sm">
                                        <option value="">Pilih Kategori</option>
                                        @foreach($kategoris as $k)
                                            <option value="{{ $k->id }}">{{ $k->nama_kategori }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            @if (!$cabangId)
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1">Cabang</label>
                                    <select name="cabang_id" required x-model="expense.cabang_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500 shadow-sm">
                                        <option value="">Pilih Cabang</option>
                                        @foreach($cabangs as $c)
                                            <option value="{{ $c->id }}">{{ $c->nama_cabang }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Nominal (Rp)</label>
                                <input type="number" name="nominal" required x-model="expense.nominal" placeholder="Contoh: 500000" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500 shadow-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1">Keterangan</label>
                                <textarea name="keterangan" required x-model="expense.keterangan" rows="3" placeholder="Deskripsi pengeluaran..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500 shadow-sm"></textarea>
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-end gap-3">
                            <button type="button" @click="isOpen = false" class="px-4 py-2 text-sm font-medium text-slate-700 hover:text-slate-900">Batal</button>
                            <button type="submit" class="rounded-lg bg-blue-600 px-6 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-700 transition" x-text="isEdit ? 'Perbarui' : 'Simpan'"></button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</x-layouts.dashboard-shell>
