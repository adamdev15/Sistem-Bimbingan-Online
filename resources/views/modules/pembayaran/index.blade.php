<x-layouts.dashboard-shell title="Pembayaran — eBimbel">
    <x-module-page-header
        title="Pembayaran & tagihan"
        description="{{ auth()->user()->hasRole('siswa') ? 'Rincian tagihan dan riwayat pembayaran Anda.' : 'Kelola invoice, kategori biaya, dan rekonsiliasi kas per cabang.' }}"
    >
        <x-slot name="actions">
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                <button type="button" class="inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                    Buat tagihan massal
                </button>
            @endif
        </x-slot>
    </x-module-page-header>

    <div class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option>Periode — Maret 2026</option>
            <option>Februari 2026</option>
        </select>
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
            <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option>Cabang — semua</option>
                <option>Kelapa Gading</option>
            </select>
        @endif
        <select class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option>Status — semua</option>
            <option>Lunas</option>
            <option>Pending</option>
            <option>Terlambat</option>
        </select>
        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
            <input type="search" placeholder="Cari siswa…" class="min-w-[180px] rounded-lg border border-slate-200 px-3 py-2 text-sm">
        @endif
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Total terbit</p>
            <p class="mt-1 text-xl font-bold text-slate-900">Rp 124,5 jt</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Sudah masuk</p>
            <p class="mt-1 text-xl font-bold text-emerald-700">Rp 98,2 jt</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Outstanding</p>
            <p class="mt-1 text-xl font-bold text-amber-700">Rp 26,3 jt</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3">No. invoice</th>
                        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                            <th class="px-4 py-3">Siswa</th>
                        @endif
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Jatuh tempo</th>
                        <th class="px-4 py-3">Nominal</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ([
                        ['INV-2026-0312', 'Alya Putri', 'SPP April', '5 Apr 2026', 'Rp 450.000', 'pending'],
                        ['INV-2026-0298', 'Budi Santoso', 'Modul Try Out', '28 Mar 2026', 'Rp 125.000', 'lunas'],
                        ['INV-2026-0301', auth()->user()->hasRole('siswa') ? 'Anda' : 'Citra L.', 'Registrasi', '15 Mar 2026', 'Rp 200.000', 'lunas'],
                    ] as $pay)
                        <tr class="text-slate-700">
                            <td class="px-4 py-3 font-mono text-xs">{{ $pay[0] }}</td>
                            @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                                <td class="px-4 py-3 font-medium">{{ $pay[1] }}</td>
                            @endif
                            <td class="px-4 py-3">{{ $pay[2] }}</td>
                            <td class="px-4 py-3">{{ $pay[3] }}</td>
                            <td class="px-4 py-3">{{ $pay[4] }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $pay[5] === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $pay[5] === 'lunas' ? 'Lunas' : 'Belum bayar' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if ($pay[5] === 'lunas')
                                    <button type="button" class="text-slate-600 hover:underline">Kwitansi</button>
                                @else
                                    <button type="button" class="font-medium text-blue-600 hover:underline">Bayar</button>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.dashboard-shell>
