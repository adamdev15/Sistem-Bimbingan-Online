<x-layouts.dashboard-shell title="Cabang — eBimbel">
    <x-module-page-header
        title="Master cabang"
        description="Kelola lokasi, kontak penanggung jawab, dan status operasional. Prototipe siap dihubungkan ke model Branch."
    >
        <x-slot name="actions">
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Tambah cabang
            </button>
        </x-slot>
    </x-module-page-header>

    <div class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <label class="block text-xs font-medium text-slate-500">Cari</label>
        <input type="search" placeholder="Nama / kode cabang" class="min-w-[200px] rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        <label class="block text-xs font-medium text-slate-500">Kota</label>
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
            <option>Semua</option>
            <option>Jakarta</option>
            <option>Bandung</option>
        </select>
        <label class="flex items-center gap-2 text-sm text-slate-600">
            <input type="checkbox" class="rounded border-slate-300 text-blue-600" checked> Hanya aktif
        </label>
        <button type="button" class="ml-auto rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset filter</button>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama cabang</th>
                        <th class="px-4 py-3">Kota</th>
                        <th class="px-4 py-3">PIC</th>
                        <th class="px-4 py-3">Siswa aktif</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ([
                        ['CBG-JKT-01', 'eBimbel Kelapa Gading', 'Jakarta', 'Bu Rina', '412', 'aktif'],
                        ['CBG-BDG-02', 'eBimbel Dago', 'Bandung', 'Pak Dedi', '198', 'aktif'],
                        ['CBG-JKT-03', 'eBimbel Ciledug', 'Tangerang', 'Bu Maya', '0', 'rekonstruksi'],
                    ] as $row)
                        <tr class="text-slate-700">
                            <td class="px-4 py-3 font-mono text-xs">{{ $row[0] }}</td>
                            <td class="px-4 py-3 font-medium">{{ $row[1] }}</td>
                            <td class="px-4 py-3">{{ $row[2] }}</td>
                            <td class="px-4 py-3">{{ $row[3] }}</td>
                            <td class="px-4 py-3">{{ $row[4] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $row[5] === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $row[5] === 'aktif' ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" class="text-blue-600 hover:underline">Edit</button>
                                <span class="text-slate-300">·</span>
                                <button type="button" class="text-slate-600 hover:underline">Detail</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between border-t border-slate-100 px-4 py-3 text-xs text-slate-500">
            <span>Menampilkan 3 dari 8 cabang</span>
            <div class="flex gap-2">
                <button type="button" class="rounded border border-slate-200 px-2 py-1 hover:bg-slate-50">Sebelumnya</button>
                <button type="button" class="rounded border border-slate-200 px-2 py-1 hover:bg-slate-50">Berikutnya</button>
            </div>
        </div>
    </div>
</x-layouts.dashboard-shell>
