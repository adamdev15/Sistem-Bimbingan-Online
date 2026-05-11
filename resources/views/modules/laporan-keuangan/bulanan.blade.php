<x-layouts.dashboard-shell title="Laporan Mitra — Jarimatrik">
    <x-module-page-header title="Laporan & Analisa Mitra {{ $cabang ? $cabang->nama_cabang : 'Seluruh Cabang' }}" description="Rekapitulasi laba bersih dan pembagian hasil mitra untuk periode {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}.">
        <x-slot name="actions">
            @php $currentCabangId = $cabang ? $cabang->id : 'all'; @endphp
            <a href="{{ route('laporan-keuangan.index', ['month' => $month, 'cabang_id' => $currentCabangId]) }}" class="inline-flex rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Kembali
            </a>
            <a href="{{ route('laporan-keuangan.export.pdf', ['type' => 'mitra', 'month' => $month, 'cabang_id' => $currentCabangId]) }}" class="inline-flex rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                Cetak Laporan
            </a>
        </x-slot>
    </x-module-page-header>

    <div class="mx-auto max-w-4xl">

        <div class="overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl print:shadow-none print:border-slate-800">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-300">
                        <th class="border-r border-slate-300 px-6 py-4 text-center w-12">NO</th>
                        <th class="border-r border-slate-300 px-6 py-4 text-left">KETERANGAN</th>
                        <th class="border-r border-slate-300 px-6 py-4 text-right">PEMASUKAN</th>
                        <th class="border-r border-slate-300 px-6 py-4 text-right">PENGELUARAN</th>
                        <th class="px-6 py-4 text-right">SALDO</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    <!-- PEMASUKAN SECTION -->
                    @php $no = 1; @endphp
                    @foreach ($incomeBreakdown as $income)
                        <tr class="text-slate-700">
                            <td class="border-r border-slate-200 px-6 py-3 text-center text-slate-400 font-mono">{{ $no++ }}.</td>
                            <td class="border-r border-slate-200 px-6 py-3 font-medium">{{ $income->nama_biaya }}</td>
                            <td class="border-r border-slate-200 px-6 py-3 text-right font-semibold text-emerald-700">
                                {{ number_format($income->total, 0, ',', '.') }}
                            </td>
                            <td class="border-r border-slate-200 px-6 py-3 text-right text-slate-300">—</td>
                            <td class="px-6 py-3 text-right text-slate-300">—</td>
                        </tr>
                    @endforeach

                    <!-- PENGELUARAN SECTION -->
                    @foreach ($expenseBreakdown as $expense)
                        <tr class="text-slate-700">
                            <td class="border-r border-slate-200 px-6 py-3 text-center text-slate-400 font-mono">{{ $no++ }}.</td>
                            <td class="border-r border-slate-200 px-6 py-3 font-medium">{{ $expense->nama_kategori }}</td>
                            <td class="border-r border-slate-200 px-6 py-3 text-right text-slate-300">—</td>
                            <td class="border-r border-slate-200 px-6 py-3 text-right font-semibold text-rose-700">
                                {{ number_format($expense->total, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3 text-right text-slate-300">—</td>
                        </tr>
                    @endforeach

                    <!-- SALARY (HONOR GURU) -->
                    <tr class="text-slate-700">
                        <td class="border-r border-slate-200 px-6 py-3 text-center text-slate-400 font-mono">{{ $no++ }}.</td>
                        <td class="border-r border-slate-200 px-6 py-3 font-medium">Honor Guru (Gaji)</td>
                        <td class="border-r border-slate-200 px-6 py-3 text-right text-slate-300">—</td>
                        <td class="border-r border-slate-200 px-6 py-3 text-right font-semibold text-rose-700">
                            {{ number_format($totalSalaries, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3 text-right text-slate-300">—</td>
                    </tr>

                    <!-- JUMLAH ROW -->
                    <tr class="text-slate-900 font-black">
                        <td colspan="2" class="border-r border-slate-700 px-6 py-4 text-center text-xs uppercase tracking-widest">JUMLAH</td>
                        <td class="border-r border-slate-700 px-6 py-4 text-right">
                            {{ number_format($totalIncome, 0, ',', '.') }}
                        </td>
                        <td class="border-r border-slate-700 px-6 py-4 text-right">
                            {{ number_format($totalExpenses, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            {{ number_format($netProfit, 0, ',', '.') }}
                        </td>
                    </tr>

                    <!-- BAGI HASIL SECTION -->
                    <tr class="font-black text-slate-800">
                        <td colspan="5" class="px-6 py-2 border-b border-slate-300 text-xs">BAGI HASIL (BERDASARKAN LABA BERSIH)</td>
                    </tr>
                    <tr class="text-slate-800">
                        <td class="border-r border-slate-200 px-6 py-4 text-center font-mono">a.</td>
                        <td class="border-r border-slate-200 px-6 py-4">
                            <span class="font-bold">Investor</span>
                            <span class="ml-2 text-xs font-normal text-slate-500">({{ number_format($shares['investor_pct'], 1) }}% x {{ number_format($netProfit, 0, ',', '.') }})</span>
                        </td>
                        <td class="border-r border-slate-200 px-6 py-4 text-right text-slate-300">—</td>
                        <td class="border-r border-slate-200 px-6 py-4 text-right font-bold text-slate-900">
                            {{ number_format($shares['investor_amount'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-slate-300">—</td>
                    </tr>
                    <tr class="text-slate-800">
                        <td class="border-r border-slate-200 px-6 py-4 text-center font-mono">b.</td>
                        <td class="border-r border-slate-200 px-6 py-4">
                            <span class="font-bold">Pusat</span>
                            <span class="ml-2 text-xs font-normal text-slate-500">({{ number_format($shares['pusat_pct'], 1) }}% x {{ number_format($netProfit, 0, ',', '.') }})</span>
                        </td>
                        <td class="border-r border-slate-200 px-6 py-4 text-right text-slate-300">—</td>
                        <td class="border-r border-slate-200 px-6 py-4 text-right font-bold text-slate-900">
                            {{ number_format($shares['pusat_amount'], 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right text-slate-300">—</td>
                    </tr>
                </tbody>
            </table>

            <!-- Signatures Section -->
            <div class="p-8 mt-4 grid grid-cols-2 gap-8 text-center text-sm font-bold text-slate-900 border-t border-slate-200">
                <div class="flex flex-col items-center">
                    <p class="mb-20 uppercase font-black tracking-widest text-slate-400 text-[10px]">Investor {{ $cabang ? 'Cabang ' . $cabang->nama_cabang : 'Seluruh Cabang' }}</p>
                    <div class="w-48 border-b-2 border-slate-900"></div>
                </div>
                <div class="flex flex-col items-center">
                    <p class="mb-2 text-slate-400 font-normal">{{ $cabang ? $cabang->kota : 'Tegal' }}, {{ now()->translatedFormat('d F Y') }}</p>
                    <p class="mb-20 uppercase font-black tracking-widest text-slate-400 text-[10px]">{{ $cabang ? 'Admin Cabang' : 'Pusat' }}</p>
                    <div class="w-48 border-b-2 border-slate-900"></div>
                    @if($cabang && $cabang->user)
                        <p class="mt-2 uppercase font-black">{{ $cabang->user->name }}</p>
                        <p class="text-xs font-normal text-slate-500">{{ $cabang->nama_cabang }}</p>
                    @else
                        <p class="mt-2 uppercase font-black">Admin Pusat Jarimatrik</p>
                        <p class="text-xs font-normal text-slate-500">Pusat Jarimatrik</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-8 rounded-xl bg-orange-50 border border-orange-200 p-4 text-xs text-orange-800 print:hidden">
            <strong>Catatan:</strong> Laporan ini adalah ringkasan performa bulanan. Pastikan semua tagihan SPP dan pengeluaran operasional bulan ini telah divalidasi kebenarannya sebelum melakukan distribusi bagi hasil.
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .rounded-2xl { border-radius: 0 !important; }
            table { border-color: black !important; }
            th, td { border-color: black !important; }
        }
    </style>
</x-layouts.dashboard-shell>
