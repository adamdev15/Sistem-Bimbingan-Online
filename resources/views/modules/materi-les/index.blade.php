<x-layouts.dashboard-shell title="Materi Les">
    <div x-data="{
        showModal: false,
        isEdit: false,
        removeId: null,
        formData: {
            id: '',
            nama_materi: '',
            deskripsi: '',
            pertemuan_per_minggu: 3
        },
        openModal(editMode = false) {
            this.isEdit = editMode;
            if (!editMode) {
                this.formData = {
                    id: '',
                    nama_materi: '',
                    deskripsi: '',
                    pertemuan_per_minggu: 3
                };
            }
            this.showModal = true;
        },
        doEdit(item) {
            this.isEdit = true;
            this.formData = {
                id: item.id,
                nama_materi: item.nama_materi,
                deskripsi: item.deskripsi || '',
                pertemuan_per_minggu: item.pertemuan_per_minggu
            };
            this.showModal = true;
        },
        showPriceModal: false,
        priceData: {
            id: '',
            biaya_daftar: 0,
            biaya_spp: 0,
            nama_materi: ''
        },
        openPriceModal(price, item) {
            this.priceData = {
                id: price ? price.id : '',
                materi_les_id: item.id,
                biaya_daftar: price ? parseInt(price.biaya_daftar) : 0,
                biaya_spp: price ? parseInt(price.biaya_spp) : 0,
                nama_materi: item.nama_materi
            };
            this.showPriceModal = true;
        },
    }">
        <div class="space-y-6">
            <x-module-page-header title="Materi Les" description="Kelola informasi program materi les, biaya pendaftaran, dan paket pertemuan.">
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
            @if($errors->any())
                <div class="rounded-xl bg-red-50 p-4 border border-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Terdapat kesalahan pada inputan Anda:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul role="list" class="list-disc space-y-1 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 p-5">

                {{-- FILTER + ACTION --}}
                <div class="flex flex-wrap items-end gap-3 p-4 border-b border-slate-100 mb-6">
                    <form method="GET" action="{{ route('materi-les.index') }}" class="flex flex-wrap items-end gap-3 flex-1">
                        <div class="min-w-[240px]">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari Materi</label>
                            <input
                                name="search"
                                value="{{ request('search') }}"
                                type="search"
                                placeholder="Nama materi..."
                                class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15"
                            >
                        </div>
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 transition-all">Filter</button>
                        <a href="{{ route('materi-les.index') }}" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-all">Reset</a>
                    </form>

                    {{-- BUTTON RIGHT --}}
                    @role('super_admin')
                    <div class="flex flex-wrap items-center gap-2 ml-auto">
                        <a href="{{ route('branch-prices.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-emerald-600/20 hover:bg-emerald-700 transition-all">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Kelola Harga Cabang
                        </a>
                        <button type="button" @click="openModal()" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 hover:bg-blue-700 transition-all">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Tambah Materi
                        </button>
                    </div>
                    @endrole
                </div>

                {{-- TABLE --}}
                <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-300">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-slate-700 sm:pl-6 lg:pl-8">No</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-700">Materi / Foto</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-700">Deskripsi</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-700">Pertemuan</th>
                                    @role('admin_cabang')
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-slate-700">Harga Cabang</th>
                                    @endrole
                                    @role('super_admin')
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 lg:pr-8">
                                        <span class="sr-only">Aksi</span>
                                    </th>
                                    @endrole
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white">
                                @forelse ($materiLes as $index => $item)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-slate-700 sm:pl-6 lg:pl-8">
                                            {{ $materiLes->firstItem() + $index }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    @if($item->foto)
                                                        <img class="h-10 w-10 rounded-full object-cover ring-2 ring-slate-100" src="{{ asset('image/materi/' . $item->foto) }}" alt="{{ $item->nama_materi }}">
                                                    @else
                                                        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center ring-2 ring-slate-100">
                                                            <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="ml-4">
                                                    <div class="font-medium text-slate-900">{{ $item->nama_materi }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 text-sm text-slate-700 max-w-sm truncate" title="{{ $item->deskripsi }}">
                                            {{ $item->deskripsi ?? '-' }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-700">
                                            {{ $item->pertemuan_per_minggu }}x / Minggu
                                        </td>
                                        @role('admin_cabang')
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-700">
                                            @php 
                                                $price = $item->branchPrices->first(); 
                                                $isSet = $price && ($price->biaya_daftar > 0 || $price->biaya_spp > 0);
                                            @endphp
                                            @if($isSet)
                                                <div class="flex flex-col">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                        <span class="font-semibold text-slate-700 text-xs">Daftar: Rp{{ number_format($price->biaya_daftar, 0, ',', '.') }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 mt-1">
                                                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                                        <span class="font-semibold text-slate-700 text-xs">SPP: Rp{{ number_format($price->biaya_spp, 0, ',', '.') }}</span>
                                                    </div>
                                                    <button type="button" @click="openPriceModal({{ json_encode($price) }}, {{ json_encode($item) }})" class="mt-2 inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-50 text-[10px] text-blue-600 font-bold uppercase hover:bg-blue-100 transition-colors">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                        Edit Harga
                                                    </button>
                                                </div>
                                            @else
                                                <div class="flex flex-col gap-2">
                                                    <span class="text-slate-400 italic text-xs">Belum diatur</span>
                                                    <button type="button" @click="openPriceModal({{ json_encode($price) }}, {{ json_encode($item) }})" class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-600 text-xs font-bold hover:bg-emerald-100 transition-all border border-emerald-100">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                        Atur Harga
                                                    </button>
                                                </div>
                                            @endif
                                        </td>
                                        @endrole
                                        @role('super_admin')
                                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 lg:pr-8">
                                            <div class="flex items-center justify-end gap-3">
                                                <button type="button" @click="doEdit({{ json_encode($item) }})" class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 hover:bg-yellow-100 p-2 rounded-lg transition-colors" title="Edit">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                                </button>
                                                 <form method="POST" action="{{ route('materi-les.destroy', $item) }}" class="inline" onsubmit="event.preventDefault(); confirmDelete(this, 'Hapus materi les {{ $item->nama_materi }}?')">
                                                     @csrf @method('DELETE')
                                                     <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors" title="Hapus">
                                                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                     </button>
                                                 </form>
                                            </div>
                                        </td>
                                        @endrole
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-3 py-8 text-center text-sm text-slate-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                                <p class="mt-4 text-base font-semibold text-slate-900">Belum ada data materi les</p>
                                                <p class="mt-1 text-slate-500">Mulai dengan menambahkan materi les baru.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                    </table>
                </div>
            </div>
            @if($materiLes->hasPages())
                <div class="mt-4">
                    {{ $materiLes->links() }}
                </div>
            @endif
        </div>

        @role('super_admin')
        <!-- Modal Create/Edit -->
        <div x-show="showModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-xl">
                        <form :action="isEdit ? '{{ url('/materi-les') }}/' + formData.id : '{{ route('materi-les.store') }}'" method="POST" enctype="multipart/form-data" class="divide-y divide-slate-100">
                            @csrf
                            <template x-if="isEdit">
                                <input type="hidden" name="_method" value="PUT">
                            </template>
                            <div class="px-6 py-5 flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-semibold leading-6 text-slate-900" id="modal-title" x-text="isEdit ? 'Edit Materi Les' : 'Tambah Materi Les'"></h3>
                                    <p class="mt-2 text-sm text-slate-500">Isi informasi untuk materi les / program belajar bimbel ini.</p>
                                </div>
                                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="px-6 py-6 space-y-6">
                                <div>
                                    <label for="nama_materi" class="block text-sm font-medium leading-6 text-slate-900">Nama Materi Les <span class="text-red-500">*</span></label>
                                    <div class="mt-2 text-sm">
                                        <input type="text" name="nama_materi" id="nama_materi" x-model="formData.nama_materi" required class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                    </div>
                                </div>
                                
                                    <div>
                                        <label for="pertemuan_per_minggu" class="block text-sm font-medium leading-6 text-slate-900">Pertemuan / Minggu <span class="text-red-500">*</span></label>
                                        <div class="mt-2 text-sm">
                                            <input type="number" name="pertemuan_per_minggu" id="pertemuan_per_minggu" x-model="formData.pertemuan_per_minggu" required min="1" class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                                        </div>
                                    </div>
                                

                                <div>
                                    <label for="foto" class="block text-sm font-medium leading-6 text-slate-900">Gambar Cover/Brosur Materi</label>
                                    <div class="mt-2 text-sm">
                                        <input type="file" name="foto" id="foto" accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    </div>
                                    <template x-if="isEdit">
                                        <p class="mt-2 text-xs text-slate-500">Kosongkan jika tidak ingin mengubah gambar.</p>
                                    </template>
                                </div>

                                <div>
                                    <label for="deskripsi" class="block text-sm font-medium leading-6 text-slate-900">Deskripsi</label>
                                    <div class="mt-2 text-sm">
                                        <textarea name="deskripsi" id="deskripsi" rows="3" x-model="formData.deskripsi" class="block w-full rounded-xl border-0 py-2.5 px-3.5 text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-2xl">
                                <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:w-auto transition-colors">
                                    Simpan Materi
                                </button>
                                <button type="button" @click="showModal = false" class="mt-3 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @endrole
        @role('admin_cabang')
        <!-- Modal Edit Harga (Khusus Admin Cabang) -->
        <div x-show="showPriceModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-cloak>
            <div x-show="showPriceModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div x-show="showPriceModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                        <form action="{{ route('branch-prices.update') }}" method="POST" class="divide-y divide-slate-100">
                            @csrf
                            @method('PUT')
                            <input type="hidden" :name="'prices[' + priceData.materi_les_id + '][id]'" :value="priceData.id">
                            <input type="hidden" :name="'prices[' + priceData.materi_les_id + '][materi_les_id]'" :value="priceData.materi_les_id">
                            
                            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                                <h3 class="text-base font-bold text-slate-900" id="modal-title" x-text="priceData.id ? 'Edit Harga Materi' : 'Atur Harga Cabang'"></h3>
                                <button type="button" @click="showPriceModal = false" class="text-slate-400 hover:text-slate-500">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <div class="px-6 py-6 space-y-5 bg-white">
                                <div class="p-3 rounded-xl bg-blue-50 border border-blue-100">
                                    <p class="text-[10px] uppercase tracking-widest font-black text-blue-400">Materi Les</p>
                                    <p class="text-sm font-bold text-blue-900 mt-0.5" x-text="priceData.nama_materi"></p>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Biaya Pendaftaran (Rp)</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 font-bold">Rp</span>
                                        <input type="number" :name="'prices[' + priceData.materi_les_id + '][biaya_daftar]'" x-model="priceData.biaya_daftar" required min="0" class="block w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-slate-700 mb-2">Biaya SPP Bulanan (Rp)</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 font-bold">Rp</span>
                                        <input type="number" :name="'prices[' + priceData.materi_les_id + '][biaya_spp]'" x-model="priceData.biaya_spp" required min="0" class="block w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                    </div>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-slate-50 flex flex-row-reverse gap-3">
                                <button type="submit" class="inline-flex w-full justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 sm:w-auto transition-all active:scale-95">
                                    Simpan Harga
                                </button>
                                <button type="button" @click="showPriceModal = false" class="inline-flex w-full justify-center rounded-xl bg-white px-6 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:w-auto transition-all">
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endrole
    </div>
</x-layouts.dashboard-shell>
