<x-layouts.dashboard-shell title="Jadwal — eBimbel">
    <x-module-page-header
        title="Jadwal & sesi kelas"
        description="Kalender mingguan, ruang, tutor, dan kapasitas. Super Admin / Admin melihat semua cabang; Tutor & Siswa melihat jadwal mereka."
    >
        <x-slot name="actions">
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                <button type="button" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                    Buat sesi baru
                </button>
            @endif
        </x-slot>
    </x-module-page-header>

    <div class="mb-6 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
        <div class="flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1">Minggu</label>
                <input type="week" value="2026-W14" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            </div>
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option>Cabang — semua</option>
                    <option>Kelapa Gading</option>
                </select>
            @endif
            <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option>Tampilan — grid</option>
                <option>Daftar</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">← Minggu lalu</button>
            <button type="button" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Minggu depan →</button>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="grid grid-cols-7 divide-x divide-slate-100 border-b border-slate-200 bg-slate-50 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
            @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $d)
                <div class="px-2 py-3">{{ $d }}</div>
            @endforeach
        </div>
        <div class="grid min-h-[280px] grid-cols-7 divide-x divide-slate-100 text-sm">
            @foreach (range(0, 6) as $i)
                <div class="space-y-2 p-2 align-top">
                    @if ($i < 3)
                        <div class="rounded-lg border border-blue-100 bg-blue-50/80 p-2">
                            <p class="font-semibold text-slate-900">08:00 Mat</p>
                            <p class="text-xs text-slate-600">R. A2 · Pak Andi</p>
                        </div>
                    @endif
                    @if ($i === 4)
                        <div class="rounded-lg border border-indigo-100 bg-indigo-50/80 p-2">
                            <p class="font-semibold text-slate-900">16:00 Try Out</p>
                            <p class="text-xs text-slate-600">Aula gabungan</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="text-base font-semibold text-slate-900">Daftar sesi (contoh)</h2>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="text-left text-xs font-semibold uppercase text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">Hari / tanggal</th>
                        <th class="py-2 pr-4">Waktu</th>
                        <th class="py-2 pr-4">Kelas</th>
                        <th class="py-2 pr-4">Tutor</th>
                        <th class="py-2 pr-4">Ruang</th>
                        <th class="py-2">Kapasitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach ([
                        ['Sen, 31 Mar', '08:00–09:30', 'X IPA 2', 'Andi W.', 'A2', '24/28'],
                        ['Sel, 1 Apr', '13:00–14:30', 'XI IPS 1', 'Sarah M.', 'Online', '18/20'],
                    ] as $j)
                        <tr>
                            <td class="py-3 pr-4">{{ $j[0] }}</td>
                            <td class="py-3 pr-4">{{ $j[1] }}</td>
                            <td class="py-3 pr-4">{{ $j[2] }}</td>
                            <td class="py-3 pr-4">{{ $j[3] }}</td>
                            <td class="py-3 pr-4">{{ $j[4] }}</td>
                            <td class="py-3">{{ $j[5] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.dashboard-shell>
