<x-layouts.dashboard-shell title="Tutor — eBimbel">
    <x-module-page-header
        title="Data tutor"
        description="Mapel, jam mengajar, dan penugasan cabang. Integrasi nanti dengan model Tutor & jadwal."
    >
        <x-slot name="actions">
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                Undang tutor
            </button>
        </x-slot>
    </x-module-page-header>

    <div class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="search" placeholder="Nama tutor" class="min-w-[200px] rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option>Mapel — semua</option>
            <option>Matematika</option>
            <option>Bahasa Inggris</option>
            <option>Fisika</option>
        </select>
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option>Status — semua</option>
            <option>Aktif</option>
            <option>Cuti</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Mapel utama</th>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Sesi / minggu</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ([
                        ['T-102', 'Dr. Andi Wijaya', 'Matematika', 'KG + Dago', '18', 'aktif'],
                        ['T-088', 'Sarah Melati, M.Pd', 'B. Inggris', 'KG', '12', 'aktif'],
                        ['T-095', 'Rizki Pratama', 'Fisika', 'Dago', '10', 'cuti'],
                    ] as $t)
                        <tr class="text-slate-700">
                            <td class="px-4 py-3 font-mono text-xs">{{ $t[0] }}</td>
                            <td class="px-4 py-3 font-medium">{{ $t[1] }}</td>
                            <td class="px-4 py-3">{{ $t[2] }}</td>
                            <td class="px-4 py-3">{{ $t[3] }}</td>
                            <td class="px-4 py-3">{{ $t[4] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $t[5] === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                    {{ ucfirst($t[5]) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button type="button" class="text-blue-600 hover:underline">Jadwal</button>
                                <button type="button" class="text-slate-600 hover:underline">Edit</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.dashboard-shell>
