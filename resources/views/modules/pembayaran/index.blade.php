@php
    $isSiswa = auth()->user()->hasRole('siswa');
@endphp
<x-layouts.dashboard-shell title="Pembayaran ? eBimbel">
    <div x-data="{ massOpen: false }">
        <x-module-page-header
            title="Pembayaran & tagihan"
            :description="$isSiswa ? 'Rincian tagihan dan riwayat pembayaran Anda.' : 'Kelola invoice, kategori biaya, dan rekonsiliasi kas per cabang.'"
        >
            <x-slot name="actions">
                @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                    <button @click="massOpen = true" type="button" class="inline-flex rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-blue-700">
                        Buat tagihan massal
                    </button>
                @endif
            </x-slot>
        </x-module-page-header>

        <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm ring-1 ring-slate-900/5">
            @if ($isSiswa)
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Periode</label>
                    <input type="month" name="bulan" value="{{ $filters['bulan'] ?? '' }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
            @endif
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="">Semua status</option>
                    <option value="lunas" @selected(($filters['status'] ?? '') === 'lunas')>Lunas</option>
                    <option value="belum" @selected(($filters['status'] ?? '') === 'belum')>Belum lunas</option>
                </select>
            </div>
            @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Siswa</label>
                    <select name="student_id" class="min-w-[200px] rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Semua siswa</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}" @selected(($filters['student_id'] ?? null) == $student->id)>{{ $student->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bulan transaksi</label>
                    <input type="month" name="bulan" value="{{ $filters['bulan'] ?? '' }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
            @endif
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Terapkan</button>
            <a href="{{ route('pembayaran.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Reset</a>
        </form>

        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            @if ($isSiswa)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">Total tagihan (filter)</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">Rp {{ number_format((int) ($summary['total'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/80 p-4 shadow-sm">
                    <p class="text-xs font-medium text-emerald-800">Sudah dibayar</p>
                    <p class="mt-1 text-xl font-bold text-emerald-800">Rp {{ number_format((int) ($summary['paid'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-emerald-700">{{ $summary['lunas_count'] ?? 0 }} transaksi lunas</p>
                </div>
                <div class="rounded-xl border border-amber-100 bg-amber-50/80 p-4 shadow-sm">
                    <p class="text-xs font-medium text-amber-800">Menunggu pembayaran</p>
                    <p class="mt-1 text-xl font-bold text-amber-900">Rp {{ number_format((int) ($summary['outstanding'] ?? 0), 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-amber-800">{{ $summary['belum_count'] ?? 0 }} tagihan belum lunas</p>
                </div>
            @else
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">Total terbit</p>
                    <p class="mt-1 text-xl font-bold text-slate-900">Rp {{ number_format((int) ($summary['total'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">Sudah masuk</p>
                    <p class="mt-1 text-xl font-bold text-emerald-700">Rp {{ number_format((int) ($summary['paid'] ?? 0), 0, ',', '.') }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-medium text-slate-500">Outstanding</p>
                    <p class="mt-1 text-xl font-bold text-amber-700">Rp {{ number_format((int) ($summary['outstanding'] ?? 0), 0, ',', '.') }}</p>
                </div>
            @endif
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm ring-1 ring-slate-900/5">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3">No. referensi</th>
                            @if (! $isSiswa)
                                <th class="px-4 py-3">Siswa</th>
                            @endif
                            <th class="px-4 py-3">Item biaya</th>
                            <th class="px-4 py-3">Tanggal</th>
                            <th class="px-4 py-3">Nominal</th>
                            <th class="px-4 py-3">Status</th>
                            @if (! $isSiswa)
                                <th class="px-4 py-3 text-right">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse ($payments as $pay)
                            <tr>
                                <td class="px-4 py-3 font-mono text-xs">INV-{{ now()->year }}-{{ str_pad((string) $pay->id, 4, '0', STR_PAD_LEFT) }}</td>
                                @if (! $isSiswa)
                                    <td class="px-4 py-3 font-medium">{{ optional($pay->siswa)->nama }}</td>
                                @endif
                                <td class="px-4 py-3">{{ optional($pay->fee)->nama_biaya }}</td>
                                <td class="px-4 py-3">{{ optional($pay->tanggal_bayar)->translatedFormat('d M Y') }}</td>
                                <td class="px-4 py-3 font-medium">Rp {{ number_format((int) $pay->nominal, 0, ',', '.') }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $pay->status === 'lunas' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ $pay->status === 'lunas' ? 'Lunas' : 'Belum lunas' }}
                                    </span>
                                </td>
                                @if (! $isSiswa)
                                    <td class="px-4 py-3 text-right">
                                        @if ($pay->status === 'lunas')
                                            <button type="button" class="text-slate-600 hover:underline">Kwitansi</button>
                                        @else
                                            <button type="button" class="font-medium text-blue-600 hover:underline">Bayar</button>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSiswa ? 5 : 7 }}" class="px-4 py-6 text-center text-slate-500">Belum ada data pembayaran.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3">{{ $payments->links() }}</div>
        </div>

        @if (auth()->user()->hasAnyRole(['super_admin', 'admin_cabang']))
            <div x-show="massOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
                <div @click.outside="massOpen = false" class="w-full max-w-2xl rounded-xl bg-white p-6">
                    <h3 class="text-lg font-semibold">Buat Tagihan Massal</h3>
                    <form method="POST" action="{{ route('pembayaran.mass.store') }}" class="mt-4 grid gap-3">
                        @csrf
                        <select name="biaya_id" class="rounded-lg border px-3 py-2">
                            @foreach ($fees as $fee)
                                <option value="{{ $fee->id }}">{{ $fee->nama_biaya }} ? Rp {{ number_format((int) $fee->nominal, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                        <input name="nominal" type="number" min="0" placeholder="Nominal" class="rounded-lg border px-3 py-2">
                        <input name="tanggal_bayar" type="date" value="{{ now()->format('Y-m-d') }}" class="rounded-lg border px-3 py-2">
                        <div class="max-h-48 overflow-y-auto rounded-lg border p-3">
                            @foreach ($students as $student)
                                <label class="mb-2 flex items-center gap-2 text-sm">
                                    <input type="checkbox" name="student_ids[]" value="{{ $student->id }}">{{ $student->nama }}
                                </label>
                            @endforeach
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="massOpen = false" class="rounded border px-3 py-2">Batal</button>
                            <button class="rounded bg-blue-600 px-3 py-2 text-white">Buat Tagihan</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-layouts.dashboard-shell>
