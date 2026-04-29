<x-layouts.dashboard-shell title="Laporan Harian — Jarimatrik">
    <x-module-page-header title="Buku Kas Harian - {{ $cabang ? $cabang->nama_cabang : 'Pusat (Konsolidasi)' }}" description="Visualisasi arus kas harian untuk periode {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}.">
        <x-slot name="actions">
            <a href="{{ route('laporan-keuangan.index', ['month' => $month, 'cabang_id' => $selectedCabangId]) }}" class="inline-flex rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Kembali
            </a>
            <a href="{{ route('laporan-keuangan.export.pdf', ['type' => 'harian', 'month' => $month, 'cabang_id' => $selectedCabangId]) }}" class="inline-flex rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700">
                Cetak Laporan
            </a>
        </x-slot>
    </x-module-page-header>

    <div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-md print:shadow-none print:border-slate-800">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse text-sm">
                <thead class="bg-white text-slate-700 text-md font-semibold border-b border-slate-300">
                    <tr>
                        <th class="border-r border-slate-300 px-4 py-3 text-center">No</th>
                        <th class="border-r border-slate-300 px-4 py-3 text-left">Tanggal</th>
                        <th class="border-r border-slate-300 px-4 py-3 text-right">Pemasukan (Rp)</th>
                        <th class="border-r border-slate-300 px-4 py-3 text-right">Pengeluaran (Rp)</th>
                        <th class="border-r border-slate-300 px-4 py-3 text-right">Jumlah (Net)</th>
                        <th class="px-4 py-3 text-right bg-white text-slate-900">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @php 
                        $totalIncome = 0; 
                        $totalExpense = 0; 
                        $runningSaldo = 0; 
                    @endphp
                    @foreach ($ledger as $index => $item)
                        @php
                            $isSunday = $item['tanggal']->isSunday();
                            $totalIncome += $item['pemasukan'];
                            $totalExpense += $item['pengeluaran'];
                            $runningSaldo = $item['saldo'];
                        @endphp
                        <tr class="{{ $isSunday ? 'bg-rose-50/30' : '' }} hover:bg-slate-50 transition">
                            <td class="border-r border-slate-200 px-4 py-3 text-center text-slate-400 font-mono">{{ $index + 1 }}</td>
                            <td class="border-r border-slate-200 px-4 py-3 font-medium text-slate-900">
                                {{ $item['tanggal']->translatedFormat('d F Y') }}
                                @if($isSunday)
                                    <span class="ml-1 text-[10px] bg-rose-100 text-rose-700 px-1.5 py-0.5 rounded font-bold uppercase tracking-tighter">Libur</span>
                                @endif
                            </td>
                            <td class="border-r border-slate-200 px-4 py-3 text-right text-emerald-700 font-semibold">
                                {{ $item['pemasukan'] > 0 ? number_format($item['pemasukan'], 0, ',', '.') : '—' }}
                            </td>
                            <td class="border-r border-slate-200 px-4 py-3 text-right text-rose-700 font-semibold">
                                {{ $item['pengeluaran'] > 0 ? number_format($item['pengeluaran'], 0, ',', '.') : '—' }}
                            </td>
                            <td class="border-r border-slate-200 px-4 py-3 text-right font-bold {{ $item['jumlah'] < 0 ? 'text-rose-600' : ($item['jumlah'] > 0 ? 'text-slate-800' : 'text-slate-300') }}">
                                {{ $item['jumlah'] != 0 ? number_format($item['jumlah'], 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-4 py-3 text-right font-black bg-slate-50 text-slate-900">
                                Rp {{ number_format($item['saldo'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-900 text-white font-bold">
                    <tr>
                        <td colspan="2" class="px-4 py-4 text-center uppercase tracking-widest text-xs">Total Akumulasi Bulan Ini</td>
                        <td class="px-4 py-4 text-right text-white">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right text-white">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right text-white">Rp {{ number_format($totalIncome - $totalExpense, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-right text-white">Rp {{ number_format($runningSaldo, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    
    <div class="mt-6 text-xs text-slate-400 italic px-2">
        * Laporan ini dihasilkan secara otomatis berdasarkan data transaksi tagihan lunas dan input pengeluaran operasional.
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; padding: 0; }
            table { border-color: black !important; }
            th, td { border-color: black !important; }
            tfoot { background-color: black !important; color: white !important; -webkit-print-color-adjust: exact; }
        }
    </style>
</x-layouts.dashboard-shell>
