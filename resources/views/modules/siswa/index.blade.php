<x-layouts.dashboard-shell title="Siswa — eBimbel">
    <x-module-page-header
        title="Data siswa"
        description="Pendaftaran, kelas, orang tua/wali, dan status akademik. Data contoh untuk tampilan prototipe."
    >
        <x-slot name="actions">
            <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Ekspor CSV
            </button>
            <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                Tambah siswa
            </button>
        </x-slot>
    </x-module-page-header>

    <div class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="search" placeholder="Nama / NIS / email" class="min-w-[220px] rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none">
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option>Cabang — semua</option>
            <option>Kelapa Gading</option>
            <option>Dago</option>
        </select>
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option>Tingkat — semua</option>
            <option>X</option>
            <option>XI</option>
            <option>XII</option>
        </select>
        <button type="button" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Terapkan</button>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">NIS</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Kelas</th>
                        <th class="px-4 py-3">Telp wali</th>
                        <th class="px-4 py-3">Status bayar</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ([
                        ['2024-001', 'Alya Putri', 'Kelapa Gading', 'X IPA 2', '0812***', 'lunas'],
                        ['2024-018', 'Budi Santoso', 'Dago', 'XI IPS 1', '0813***', 'tunggakan'],
                        ['2023-204', 'Citra Lestari', 'Kelapa Gading', 'XII IPA 1', '0811***', 'lunas'],
                    ] as $s)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $s[0] }}</td>
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $s[1] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $s[2] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $s[3] }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $s[4] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $s[5] === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $s[5] === 'lunas' ? 'Lunas' : 'Tunggakan' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" class="text-blue-600 hover:underline">Profil</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.dashboard-shell>
