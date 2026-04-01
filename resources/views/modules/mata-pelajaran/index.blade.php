@php
    $isSuper = auth()->user()->hasRole('super_admin');
    $openCreate = $errors->any() && old('form_context') === 'mapel_create';
    $openEdit = $errors->any() && old('form_context') === 'mapel_edit';
@endphp
<x-layouts.dashboard-shell title="Mata pelajaran — eBimbel">
    <div
        x-data='{
            createOpen: @json($openCreate),
            editOpen: @json($openEdit),
            edit: {
                id: @json(old("mata_pelajaran_id")),
                nama: @json(old("nama", "")),
                kode: @json(old("kode", ""))
            }
        }'
        class="space-y-6"
    >
        <x-module-page-header
            title="Mata pelajaran"
            description="Master mapel untuk jadwal. Super admin dapat menambah, mengubah, dan menghapus; admin cabang hanya melihat daftar."
        >
            @if ($isSuper)
                <x-slot name="actions">
                    <button
                        type="button"
                        @click="createOpen = true"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm ring-1 ring-blue-600/20 transition hover:bg-blue-700"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Tambah mapel
                    </button>
                </x-slot>
            @endif
        </x-module-page-header>

        @if ($errors->has('mata'))
            <p class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first('mata') }}</p>
        @endif

        @if (session('status'))
            <p class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm ring-1 ring-slate-900/5">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50/90 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3.5">Nama</th>
                        <th class="px-4 py-3.5">Kode</th>
                        @if ($isSuper)
                            <th class="px-4 py-3.5 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($mataPelajarans as $mp)
                        <tr class="transition hover:bg-slate-50/80">
                            <td class="px-4 py-3.5 font-medium text-slate-900">{{ $mp->nama }}</td>
                            <td class="px-4 py-3.5 text-slate-600">{{ $mp->kode ?? '—' }}</td>
                            @if ($isSuper)
                                <td class="px-4 py-3.5 text-right">
                                    <div class="flex flex-wrap items-center justify-end gap-3">
                                        <button
                                            type="button"
                                            @click="edit.id = {{ $mp->id }}; edit.nama = @json($mp->nama); edit.kode = @json($mp->kode ?? ''); editOpen = true"
                                            class="text-sm font-semibold text-blue-600 hover:text-blue-800"
                                        >
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('mata-pelajaran.destroy', $mp) }}" class="inline" onsubmit="return confirm('Hapus mata pelajaran ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-semibold text-rose-600 hover:text-rose-800">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isSuper ? 3 : 2 }}" class="px-4 py-10 text-center text-slate-500">
                                Belum ada data. @if ($isSuper) Gunakan tombol &ldquo;Tambah mapel&rdquo; untuk menambahkan. @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3">{{ $mataPelajarans->links() }}</div>
        </div>

        @if ($isSuper)
            {{-- Modal: tambah --}}
            <div
                x-show="createOpen"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]"
                role="dialog"
                aria-modal="true"
                aria-labelledby="mapel-create-title"
            >
                <div
                    @click.outside="createOpen = false"
                    @keydown.escape.window="createOpen = false"
                    class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="mapel-create-title" class="text-lg font-bold tracking-tight text-slate-900">Tambah mata pelajaran</h3>
                            <p class="mt-1 text-sm text-slate-500">Nama wajib; kode singkat opsional untuk laporan.</p>
                        </div>
                        <button type="button" @click="createOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" action="{{ route('mata-pelajaran.store') }}" class="mt-6 space-y-4">
                        @csrf
                        <input type="hidden" name="form_context" value="mapel_create">
                        <div>
                            <label for="create-nama" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama mapel</label>
                            <input id="create-nama" name="nama" value="{{ old('form_context') === 'mapel_create' ? old('nama') : '' }}" required placeholder="Contoh: Matematika" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none ring-blue-500/0 transition focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            @if ($errors->has('nama') && old('form_context') === 'mapel_create')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('nama') }}</p>
                            @endif
                        </div>
                        <div>
                            <label for="create-kode" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kode (opsional)</label>
                            <input id="create-kode" name="kode" value="{{ old('form_context') === 'mapel_create' ? old('kode') : '' }}" placeholder="MTK" maxlength="32" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none ring-blue-500/0 transition focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            @if ($errors->has('kode') && old('form_context') === 'mapel_create')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('kode') }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                            <button type="button" @click="createOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Modal: edit --}}
            <div
                x-show="editOpen"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4 backdrop-blur-[2px]"
                role="dialog"
                aria-modal="true"
                aria-labelledby="mapel-edit-title"
            >
                <div
                    @click.outside="editOpen = false"
                    @keydown.escape.window="editOpen = false"
                    class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-xl ring-1 ring-slate-900/5"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 id="mapel-edit-title" class="text-lg font-bold tracking-tight text-slate-900">Ubah mata pelajaran</h3>
                            <p class="mt-1 text-sm text-slate-500">Perbarui nama atau kode singkat.</p>
                        </div>
                        <button type="button" @click="editOpen = false" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600" aria-label="Tutup">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <form method="POST" :action="`{{ url('/mata-pelajaran') }}/${edit.id}`" class="mt-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="form_context" value="mapel_edit">
                        <input type="hidden" name="mata_pelajaran_id" x-model="edit.id">
                        <div>
                            <label for="edit-nama" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Nama mapel</label>
                            <input id="edit-nama" name="nama" x-model="edit.nama" required class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none ring-blue-500/0 transition focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            @if ($errors->has('nama') && old('form_context') === 'mapel_edit')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('nama') }}</p>
                            @endif
                        </div>
                        <div>
                            <label for="edit-kode" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Kode (opsional)</label>
                            <input id="edit-kode" name="kode" x-model="edit.kode" maxlength="32" class="mt-1.5 w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-900 shadow-sm outline-none ring-blue-500/0 transition focus:border-blue-300 focus:ring-4 focus:ring-blue-500/15">
                            @if ($errors->has('kode') && old('form_context') === 'mapel_edit')
                                <p class="mt-1.5 text-xs font-medium text-rose-600">{{ $errors->first('kode') }}</p>
                            @endif
                        </div>
                        <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-5">
                            <button type="button" @click="editOpen = false" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                            <button type="submit" class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Perbarui</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts.dashboard-shell>
