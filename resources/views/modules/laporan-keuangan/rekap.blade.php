<x-layouts.dashboard-shell title="Laporan Bulanan — Jarimatrik">
    <x-module-page-header title="Laporan Bulanan - {{ $cabang ? $cabang->nama_cabang : 'Semua Cabang (Konsolidasi)' }}" description="Rekapitulasi total pemasukan dan pengeluaran operasional periode {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}.">
        <x-slot name="actions">
            <a href="{{ route('laporan-keuangan.index', ['month' => $month, 'cabang_id' => $selectedCabangId]) }}" class="inline-flex rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Kembali
            </a>
            <a href="{{ route('laporan-keuangan.export.pdf', ['type' => 'bulanan', 'month' => $month, 'cabang_id' => $selectedCabangId]) }}" class="inline-flex rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                Cetak Laporan
            </a>
        </x-slot>
    </x-module-page-header>

    <div class="mx-auto max-w-4xl">
        <div class="overflow-hidden rounded-2xl border border-slate-300 bg-white shadow-xl print:shadow-none print:border-slate-800">
            <table class="w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-300">
                        <th class="border-r border-slate-300 px-6 py-4 text-center w-12 text-xs uppercase tracking-widest">NO</th>
                        <th class="border-r border-slate-300 px-6 py-4 text-left text-xs uppercase tracking-widest">KETERANGAN</th>
                        <th class="border-r border-slate-300 px-6 py-4 text-right text-xs uppercase tracking-widest">PEMASUKAN</th>
                        <th class="border-r border-slate-300 px-6 py-4 text-right text-xs uppercase tracking-widest">PENGELUARAN</th>
                        <th class="px-6 py-4 text-right text-xs uppercase tracking-widest">SALDO</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @php 
                        $no = 1; 
                        $runningSum = 0;
                    @endphp

                    {{-- PEMASUKAN --}}
                    @foreach ($rekapIncome as $item)
                        @php $runningSum += $item['nominal']; @endphp
                        <tr class="text-slate-700">
                            <td class="border-r border-slate-200 px-6 py-4 text-center text-slate-400 font-mono">{{ $no++ }}.</td>
                            <td class="border-r border-slate-200 px-6 py-4 font-medium">{{ $item['keterangan'] }}</td>
                            <td class="border-r border-slate-200 px-6 py-4 text-right font-semibold text-emerald-700">
                                {{ $item['nominal'] > 0 ? 'Rp ' . number_format($item['nominal'], 0, ',', '.') : '-' }}
                            </td>
                            <td class="border-r border-slate-200 px-6 py-4 text-right text-slate-300">—</td>
                            <td class="px-6 py-4 text-right font-medium text-slate-900 border-l border-slate-100">
                                Rp {{ number_format($runningSum, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- PENGELUARAN (Operasional) --}}
                    @php $runningSum -= $totalOperasional; @endphp
                    <tr class="text-slate-700">
                        <td class="border-r border-slate-200 px-6 py-4 text-center text-slate-400 font-mono">{{ $no++ }}.</td>
                        <td class="border-r border-slate-200 px-6 py-4 font-medium">Operasional</td>
                        <td class="border-r border-slate-200 px-6 py-4 text-right text-slate-300">—</td>
                        <td class="border-r border-slate-200 px-6 py-4 text-right font-semibold text-rose-700">
                            {{ $totalOperasional > 0 ? 'Rp ' . number_format($totalOperasional, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-slate-900 border-l border-slate-100">
                            Rp {{ number_format($runningSum, 0, ',', '.') }}
                        </td>
                    </tr>

                    {{-- TOTAL AKHIR --}}
                    <tr class="text-slate-900 font-black">
                        <td colspan="2" class="border-r border-slate-700 px-6 py-5 text-center text-xs uppercase tracking-widest">JUMLAH TOTAL</td>
                        <td class="border-r border-slate-700 px-6 py-5 text-right">
                            Rp {{ number_format($totalIncome, 0, ',', '.') }}
                        </td>
                        <td class="border-r border-slate-700 px-6 py-5 text-right">
                            Rp {{ number_format($totalOperasional, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-5 text-right">
                            Rp {{ number_format($runningSum, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>

            {{-- Signature section for professional look --}}
            <div class="p-8 grid grid-cols-2 gap-8 text-center text-sm font-bold text-slate-900 border-t border-slate-200">
                <div class="flex flex-col items-center">
                    <p class="mb-20">Investor {{ $cabang ? 'Cabang ' . $cabang->nama_cabang : 'Seluruh Cabang' }}</p>
                    <div class="w-48 border-b-2 border-slate-900"></div>
                </div>
                <div class="flex flex-col items-center">
                    <p class="mb-2 text-slate-400 font-normal">{{ $cabang ? $cabang->kota : 'Tegal' }}, {{ now()->translatedFormat('d F Y') }}</p>
                    <p class="mb-20">{{ $cabang ? 'Admin Cabang' : 'Pusat' }}</p>
                    <div class="w-48 border-b-2 border-slate-900"></div>
                    @if($cabang && $cabang->user)
                        <p class="mt-2 uppercase font-black text-xs">{{ $cabang->user->name }}</p>
                    @else
                        <p class="mt-2 uppercase font-black text-xs">Admin Pusat Jarimatrik</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-8 rounded-xl bg-blue-50 border border-blue-200 p-4 text-xs text-blue-800 print:hidden">
            <strong>Info:</strong> Laporan ini direkap berdasarkan transaksi lunas di periode bulan berjalan.
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            .rounded-2xl { border-radius: 0 !important; border: 1px solid black !important; }
            table { border-collapse: collapse !important; }
            th, td { border: 1px solid black !important; padding: 8px !important; }
            .bg-slate-900 { background-color: #f8fafc !important; color: black !important; }
            .bg-blue-600 { background-color: #f1f5f9 !important; color: black !important; }
            .text-emerald-700, .text-emerald-400 { color: black !important; }
            .text-rose-700, .text-rose-400 { color: black !important; }
            .text-white { color: black !important; }
        }
    </style>
</x-layouts.dashboard-shell>
