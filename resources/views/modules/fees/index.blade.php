<x-layouts.dashboard-shell title="Daftar Biaya">
    <div x-data="{
        showModal: false,
        isEdit: false,
        formData: {
            id: '',
            nama_biaya: '',
            deskripsi: '',
            tipe: 'sekali'
        },
        openModal(editMode = false, data = null) {
            this.isEdit = editMode;
            if (editMode && data) {
                this.formData = {
                    id: data.id,
                    nama_biaya: data.nama_biaya,
                    deskripsi: data.deskripsi,
                    tipe: data.tipe
                };
            } else {
                this.formData = {
                    id: '',
                    nama_biaya: '',
                    deskripsi: '',
                    tipe: 'sekali'
                };
            }
            this.showModal = true;
        }
    }">
        <div class="space-y-6">
            <x-module-page-header title="Daftar Biaya" description="Kelola master data biaya seperti pendaftaran, SPP bulanan, dan biaya lainnya.">
            </x-module-page-header>

            @if(session('success'))
                <div class="rounded-xl bg-green-50 p-4 border border-green-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 p-5">
                <div class="flex items-center justify-between gap-3 p-4 border-b border-slate-100 mb-6">
                    <h3 class="text-lg font-semibold text-slate-800">Data Master Biaya</h3>
                    <button type="button" @click="openModal()" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-all">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah Biaya
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-300">
                        <thead class="bg-slate-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-700 sm:pl-6">No</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-700">Nama Biaya</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-700">Deskripsi</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-700">Kategori / Tipe</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white">
                            @forelse ($fees as $index => $item)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-slate-700 sm:pl-6">
                                        {{ $fees->firstItem() + $index }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-slate-900">
                                        {{ $item->nama_biaya }}
                                    </td>
                                    <td class="px-3 py-4 text-sm text-slate-500 max-w-xs truncate">
                                        {{ $item->deskripsi ?: '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-bold {{ $item->tipe === 'bulanan' ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-700/10' : 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-700/10' }}">
                                            {{ $item->tipe === 'bulanan' ? 'Bulanan' : 'Sekali Bayar' }}
                                        </span>
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openModal(true, {{ json_encode($item) }})" class="text-blue-600 hover:text-blue-900 p-2 rounded-lg hover:bg-blue-50 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                            </button>
                                            <form action="{{ route('fees.destroy', $item) }}" method="POST" class="inline-block" onsubmit="event.preventDefault(); confirmDelete(this, 'Hapus data biaya?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 p-2 rounded-lg hover:bg-red-50 transition-colors">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-sm text-slate-500 italic">Belum ada data tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($fees->hasPages())
                <div class="mt-4">{{ $fees->links() }}</div>
            @endif
        </div>

        <!-- Modal CRUD -->
        <div x-show="showModal" class="relative z-50" x-cloak>
            <div x-show="showModal" x-transition:enter="duration-300 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showModal" x-transition:enter="duration-300 ease-out" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl">
                        <form :action="isEdit ? '{{ route('fees.index') }}/' + formData.id : '{{ route('fees.store') }}'" method="POST">
                            @csrf
                            <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>
                            
                            <div class="px-6 py-5 border-b border-slate-100">
                                <h3 class="text-xl font-bold text-slate-900" x-text="isEdit ? 'Edit Data Biaya' : 'Tambah Biaya Baru'"></h3>
                            </div>

                            <div class="px-6 py-6 space-y-5">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700">Nama Biaya</label>
                                    <input type="text" name="nama_biaya" x-model="formData.nama_biaya" required class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700">Tipe / Kategori</label>
                                    <select name="tipe" x-model="formData.tipe" required class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none">
                                        <option value="bulanan">Bulanan / SPP</option>
                                        <option value="sekali">Sekali Bayar</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700">Deskripsi</label>
                                    <textarea name="deskripsi" x-model="formData.deskripsi" rows="3" class="mt-1.5 w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all outline-none" placeholder="Keterangan tambahan..."></textarea>
                                </div>
                            </div>

                            <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-2xl border-t border-slate-100">
                                <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 focus:ring-4 focus:ring-blue-500/20 transition-all">Simpan Data</button>
                                <button type="button" @click="showModal = false" class="rounded-xl border border-slate-300 bg-white px-6 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">Batal</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard-shell>
