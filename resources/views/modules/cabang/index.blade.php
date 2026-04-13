<x-layouts.dashboard-shell title="Cabang - Bimbel Jarimatrik">
    <div
        x-data="{
            createOpen: false,
            editOpen: false,
            deleteOpen: false,
            edit: {
                id: null,
                nama_cabang: '',
                alamat: '',
                kota: '',
                telepon: '',
                status: 'aktif',
                admin_name: '',
                admin_email: '',
            },
            removeId: null,
        }"
        class="space-y-6"
    >
        <x-module-page-header title="Data cabang bimbel" description="Kelola data cabang bimbel, status operasional, dan data akun akses admin cabang.">
            
        </x-module-page-header>

        @if (session('status'))
            <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5 p-5">

            {{-- FILTER + ACTION --}}
            <div class="flex flex-wrap items-end gap-3 p-4 border-b border-slate-100 mb-4">

                <form method="GET" class="flex flex-wrap items-end gap-3 flex-1">

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Cari</label>
                        <input name="search" value="{{ $filters['search'] ?? '' }}" type="search"
                            placeholder="Nama / kota cabang"
                            class="mt-1.5 min-w-[200px] rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Kota</label>
                        <input name="kota" value="{{ $filters['kota'] ?? '' }}" type="text"
                            placeholder="Kota"
                            class="mt-1.5 rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                        <select name="active_only"
                            class="mt-1.5 rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            <option value="">Semua</option>
                            <option value="1" {{ ($filters['active_only'] ?? '') === '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ ($filters['active_only'] ?? '') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                        Filter
                    </button>

                </form>

                {{-- BUTTON RIGHT --}}
                <div class="ml-auto">
                    <button type="button" @click="createOpen = true"
                        class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">
                        + Tambah Cabang
                    </button>
                </div>
            </div>

            {{-- TABLE --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50/90 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-4 py-3.5">No</th>
                            <th class="px-4 py-3.5">Nama cabang</th>
                            <th class="px-4 py-3.5">Kota</th>
                            <th class="px-4 py-3.5">(email)</th>
                            <th class="px-4 py-3.5">Alamat</th>
                            <th class="px-4 py-3.5">Telepon</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($cabangs as $cabang)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-4 py-3.5 font-mono text-xs">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="px-4 py-3.5 font-medium text-slate-900">
                                    {{ $cabang->nama_cabang }}
                                </td>

                                <td class="px-4 py-3.5">{{ $cabang->kota }}</td>

                                <td class="px-4 py-3.5 text-slate-600">
                                    {{ optional($cabang->user)->email ?? '—' }}
                                </td>
                                <td class="px-4 py-3.5 text-slate-600">
                                    {{ $cabang->alamat ?: '—' }}
                                </td>

                                <td class="px-4 py-3.5">
                                    {{ $cabang->telepon ?: '—' }}
                                </td>

                                <td class="px-4 py-3.5">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold 
                                        {{ $cabang->status === 'aktif' 
                                            ? 'bg-emerald-100 text-emerald-800' 
                                            : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst($cabang->status) }}
                                    </span>
                                </td>

                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button"
                                            @click="editOpen = true; edit = { id: {{ $cabang->id }}, nama_cabang: @js($cabang->nama_cabang), alamat: @js($cabang->alamat), kota: @js($cabang->kota), telepon: @js($cabang->telepon), status: @js($cabang->status), admin_name: @js(optional($cabang->user)->name ?? ''), admin_email: @js(optional($cabang->user)->email ?? '') }"
                                            class="text-yellow-600 hover:text-yellow-900 bg-yellow-50 hover:bg-yellow-100 p-2 rounded-lg transition-colors" title="Edit">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                        </button>

                                        <button type="button"
                                            @click="deleteOpen = true; removeId = {{ $cabang->id }}"
                                            class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition-colors" title="Hapus">
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-500">
                                    Belum ada data cabang.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">
                {{ $cabangs->links() }}
            </div>
        </div>

        {{-- Modal: create --}}
        <div x-show="createOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true">
            <div @click.outside="createOpen = false" @keydown.escape.window="createOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-slate-900">Tambah cabang</h3>
                        <p class="mt-1 text-sm text-slate-500">Data cabang dan kredensial admin cabang (satu akun login per cabang).</p>
                    </div>
                    <button type="button" @click="createOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" action="{{ route('cabang.store') }}" class="mt-6 space-y-5">
                    @csrf
                    <div class="space-y-3 rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Data cabang</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-slate-500">Nama cabang</label>
                                <input name="nama_cabang" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500">Kota</label>
                                <input name="kota" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500">Telepon</label>
                                <input name="telepon" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-slate-500">Alamat</label>
                                <textarea name="alamat" required rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15"></textarea>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500">Status</label>
                                <select name="status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                    <option value="">Pilih status</option>
                                <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3 rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-blue-800">Akun admin cabang</p>
                        <p class="text-xs text-blue-700/90">Email harus unik di seluruh sistem. Admin dapat login dengan peran Admin Cabang.</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-slate-600">Nama admin</label>
                                <input name="admin_name" required autocomplete="name" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-slate-600">Email login</label>
                                <input name="admin_email" type="email" required autocomplete="email" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Kata sandi</label>
                                <input name="admin_password" type="password" required autocomplete="new-password" minlength="8" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Konfirmasi kata sandi</label>
                                <input name="admin_password_confirmation" type="password" required autocomplete="new-password" minlength="8" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                        <button type="button" @click="createOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: edit --}}
        <div x-show="editOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true">
            <div @click.outside="editOpen = false" @keydown.escape.window="editOpen = false" class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold tracking-tight text-slate-900">Edit cabang</h3>
                        <p class="mt-1 text-sm text-slate-500">Perbarui data cabang dan opsional akun admin terkait.</p>
                    </div>
                    <button type="button" @click="editOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="POST" :action="`{{ url('/cabang') }}/${edit.id}`" class="mt-6 space-y-5">
                    @csrf
                    @method('PUT')
                    <div class="space-y-3 rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Data cabang</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-slate-500">Nama cabang</label>
                                <input name="nama_cabang" x-model="edit.nama_cabang" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500">Kota</label>
                                <input name="kota" x-model="edit.kota" required class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500">Telepon</label>
                                <input name="telepon" x-model="edit.telepon" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-slate-500">Alamat</label>
                                <textarea name="alamat" x-model="edit.alamat" required rows="2" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15"></textarea>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-500">Status</label>
                                <select name="status" x-model="edit.status" class="mt-1 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-3 rounded-xl border border-blue-100 bg-blue-50/40 p-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-blue-800">Admin cabang</p>
                        <p class="text-xs text-blue-700/90">Kosongkan kata sandi jika tidak ingin mengubah. Isi hanya jika cabang sudah punya akun admin.</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-slate-600">Nama admin</label>
                                <input name="admin_name" x-model="edit.admin_name" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="text-xs font-semibold text-slate-600">Email login</label>
                                <input name="admin_email" type="email" x-model="edit.admin_email" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Kata sandi baru (opsional)</label>
                                <input name="admin_password" type="password" autocomplete="new-password" minlength="8" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-slate-600">Konfirmasi</label>
                                <input name="admin_password_confirmation" type="password" autocomplete="new-password" minlength="8" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                        <button type="button" @click="editOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal: delete --}}
        <div x-show="deleteOpen" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]" role="dialog" aria-modal="true">
            <div @click.outside="deleteOpen = false" @keydown.escape.window="deleteOpen = false" class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5">
                <h3 class="text-lg font-bold text-slate-900">Hapus cabang</h3>
                <p class="mt-2 text-sm text-slate-600">Cabang dan akun admin terkait akan dihapus. Data yang dihapus tidak dapat dikembalikan.</p>
                <form method="POST" :action="`{{ url('/cabang') }}/${removeId}`" class="mt-6 flex flex-wrap justify-end gap-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="deleteOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.dashboard-shell>
