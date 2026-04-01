<x-layouts.dashboard-shell title="Siswa bimbingan — eBimbel">
    <x-module-page-header
        title="Data siswa bimbingan"
        description="Siswa yang pernah tercatat kehadiran di kelas Anda. Gunakan filter untuk menemukan siswa dengan cepat."
    />

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm ring-1 ring-slate-900/5">
        <div class="flex min-w-[200px] flex-1 flex-col gap-1">
            <label for="tutor-siswa-search" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Cari</label>
            <input
                id="tutor-siswa-search"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                type="search"
                placeholder="Nama, email, atau NIK"
                class="rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500"
            />
        </div>
        <div class="flex flex-col gap-1">
            <label for="tutor-siswa-jk" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Jenis kelamin</label>
            <select id="tutor-siswa-jk" name="jenis_kelamin" class="rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm">
                <option value="">Semua</option>
                <option value="laki_laki" @selected(($filters['jenis_kelamin'] ?? '') === 'laki_laki')>Laki-laki</option>
                <option value="perempuan" @selected(($filters['jenis_kelamin'] ?? '') === 'perempuan')>Perempuan</option>
            </select>
        </div>
        <div class="flex flex-col gap-1">
            <label for="tutor-siswa-status" class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
            <select id="tutor-siswa-status" name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm shadow-sm">
                <option value="">Semua</option>
                <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif</option>
                <option value="nonaktif" @selected(($filters['status'] ?? '') === 'nonaktif')>Nonaktif</option>
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
            Terapkan
        </button>
        <a href="{{ route('tutor.siswa.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
            Reset
        </a>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-900/5">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-gradient-to-r from-slate-50 to-blue-50/50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">Ref</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Cabang</th>
                        <th class="px-4 py-3">Kontak</th>
                        <th class="px-4 py-3">Jenis kelamin</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($siswas as $siswa)
                        <tr class="transition hover:bg-blue-50/40">
                            <td class="px-4 py-3 font-mono text-xs text-slate-500">SIS-{{ str_pad((string) $siswa->id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-4 py-3 font-semibold text-slate-900">{{ $siswa->nama }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ optional($siswa->cabang)->nama_cabang ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="block text-slate-800">{{ $siswa->no_hp ?: '—' }}</span>
                                <span class="text-xs text-slate-500">{{ $siswa->email }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">
                                    {{ $siswa->jenis_kelamin === 'perempuan' ? 'Perempuan' : 'Laki-laki' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $siswa->status === 'aktif' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ ucfirst($siswa->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                <p class="font-medium text-slate-700">Belum ada siswa di bimbingan Anda</p>
                                <p class="mt-1 text-sm">Siswa akan muncul setelah ada data presensi untuk jadwal yang Anda ajar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $siswas->links() }}</div>
    </div>
</x-layouts.dashboard-shell>
