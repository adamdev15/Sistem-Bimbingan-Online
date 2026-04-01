<x-layouts.dashboard-shell title="Cabang — eBimbel">
    <div x-data="{createOpen:false,editOpen:false,deleteOpen:false,edit:{id:null,nama_cabang:'',alamat:'',kota:'',telepon:'',status:'aktif'},removeId:null}">
    <x-module-page-header title="Master cabang" description="Kelola lokasi, kontak penanggung jawab, dan status operasional.">
        <x-slot name="actions">
            <button @click="createOpen=true" type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">Tambah cabang</button>
        </x-slot>
    </x-module-page-header>

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <label class="block text-xs font-medium text-slate-500">Cari</label>
        <input name="search" value="{{ $filters['search'] ?? '' }}" type="search" placeholder="Nama / kota cabang" class="min-w-[200px] rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <label class="block text-xs font-medium text-slate-500">Kota</label>
        <input name="kota" value="{{ $filters['kota'] ?? '' }}" type="text" placeholder="Kota" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <label class="flex items-center gap-2 text-sm text-slate-600"><input name="active_only" value="1" type="checkbox" class="rounded border-slate-300 text-blue-600" {{ !empty($filters['active_only']) ? 'checked' : '' }}> Hanya aktif</label>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Terapkan</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50"><tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><th class="px-4 py-3">Kode</th><th class="px-4 py-3">Nama cabang</th><th class="px-4 py-3">Kota</th><th class="px-4 py-3">Telepon</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Aksi</th></tr></thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($cabangs as $cabang)
                        <tr class="text-slate-700">
                            <td class="px-4 py-3 font-mono text-xs">CBG-{{ str_pad((string) $cabang->id, 4, '0', STR_PAD_LEFT) }}</td><td class="px-4 py-3 font-medium">{{ $cabang->nama_cabang }}</td><td class="px-4 py-3">{{ $cabang->kota }}</td><td class="px-4 py-3">{{ $cabang->telepon ?: '-' }}</td><td class="px-4 py-3"><span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $cabang->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ ucfirst($cabang->status) }}</span></td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <button @click="editOpen=true; edit={id:{{ $cabang->id }},nama_cabang:@js($cabang->nama_cabang),alamat:@js($cabang->alamat),kota:@js($cabang->kota),telepon:@js($cabang->telepon),status:@js($cabang->status)}" type="button" class="text-blue-600 hover:underline">Edit</button>
                                <button @click="deleteOpen=true; removeId={{ $cabang->id }}" type="button" class="text-rose-600 hover:underline">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada data cabang.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div><div class="px-4 py-3">{{ $cabangs->links() }}</div>
    </div>

    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"><div @click.outside="createOpen=false" class="w-full max-w-2xl rounded-xl bg-white p-6"><h3 class="text-lg font-semibold">Tambah Cabang</h3><form method="POST" action="{{ route('cabang.store') }}" class="mt-4 grid gap-3">@csrf<input name="nama_cabang" placeholder="Nama cabang" class="rounded-lg border px-3 py-2"><input name="kota" placeholder="Kota" class="rounded-lg border px-3 py-2"><input name="telepon" placeholder="Telepon" class="rounded-lg border px-3 py-2"><textarea name="alamat" placeholder="Alamat" class="rounded-lg border px-3 py-2"></textarea><select name="status" class="rounded-lg border px-3 py-2"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select><div class="flex justify-end gap-2"><button type="button" @click="createOpen=false" class="rounded border px-3 py-2">Batal</button><button class="rounded bg-blue-600 px-3 py-2 text-white">Simpan</button></div></form></div></div>

    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"><div @click.outside="editOpen=false" class="w-full max-w-2xl rounded-xl bg-white p-6"><h3 class="text-lg font-semibold">Edit Cabang</h3><form method="POST" :action="`{{ url('/cabang') }}/${edit.id}`" class="mt-4 grid gap-3">@csrf @method('PUT')<input name="nama_cabang" x-model="edit.nama_cabang" class="rounded-lg border px-3 py-2"><input name="kota" x-model="edit.kota" class="rounded-lg border px-3 py-2"><input name="telepon" x-model="edit.telepon" class="rounded-lg border px-3 py-2"><textarea name="alamat" x-model="edit.alamat" class="rounded-lg border px-3 py-2"></textarea><select name="status" x-model="edit.status" class="rounded-lg border px-3 py-2"><option value="aktif">Aktif</option><option value="nonaktif">Nonaktif</option></select><div class="flex justify-end gap-2"><button type="button" @click="editOpen=false" class="rounded border px-3 py-2">Batal</button><button class="rounded bg-blue-600 px-3 py-2 text-white">Update</button></div></form></div></div>

    <div x-show="deleteOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4"><div @click.outside="deleteOpen=false" class="w-full max-w-md rounded-xl bg-white p-6"><h3 class="text-lg font-semibold">Hapus Cabang</h3><p class="mt-2 text-sm text-slate-600">Data yang dihapus tidak bisa dikembalikan.</p><form method="POST" :action="`{{ url('/cabang') }}/${removeId}`" class="mt-4 flex justify-end gap-2">@csrf @method('DELETE')<button type="button" @click="deleteOpen=false" class="rounded border px-3 py-2">Batal</button><button class="rounded bg-rose-600 px-3 py-2 text-white">Hapus</button></form></div></div>
    </div>
</x-layouts.dashboard-shell>
