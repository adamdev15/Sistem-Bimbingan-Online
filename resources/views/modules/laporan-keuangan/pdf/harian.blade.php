<!DOCTYPE html>
<html>
<head>
    <title>Laporan Harian - {{ $month }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #1e293b; }
        .header p { margin: 5px 0; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8fafc; color: #475569; font-weight: bold; border: 1px solid #cbd5e1; padding: 8px; text-transform: uppercase; font-size: 9px; }
        td { border: 1px solid #cbd5e1; padding: 8px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .bg-slate-50 { background-color: #f8fafc; }
        .footer { margin-top: 30px; width: 100%; }
        .footer table { border: none; }
        .footer td { border: none; text-align: center; }
        .total-row { background-color: #0f172a; color: white; font-weight: bold; }
        .total-row td { border-color: #0f172a; }
    </style>
</head>
<body>
    <div class="header">
        <h2>BUKU KAS HARIAN</h2>
        <p>{{ $cabang ? $cabang->nama_cabang : 'Pusat (Konsolidasi)' }}</p>
        <p>Periode: {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">NO</th>
                <th>TANGGAL</th>
                <th class="text-right">PEMASUKAN</th>
                <th class="text-right">PENGELUARAN</th>
                <th class="text-right">JUMLAH (NET)</th>
                <th class="text-right">SALDO AKHIR</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $totalInc = 0; 
                $totalExp = 0; 
                $running = 0; 
            @endphp
            @foreach ($ledger as $i => $item)
                @php 
                    $totalInc += $item['pemasukan']; 
                    $totalExp += $item['pengeluaran']; 
                    $running = $item['saldo'];
                @endphp
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $item['tanggal']->translatedFormat('d F Y') }}</td>
                    <td class="text-right">{{ $item['pemasukan'] > 0 ? number_format($item['pemasukan'], 0, ',', '.') : '—' }}</td>
                    <td class="text-right">{{ $item['pengeluaran'] > 0 ? number_format($item['pengeluaran'], 0, ',', '.') : '—' }}</td>
                    <td class="text-right font-bold">{{ $item['jumlah'] != 0 ? number_format($item['jumlah'], 0, ',', '.') : '—' }}</td>
                    <td class="text-right bg-slate-50">Rp {{ number_format($item['saldo'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="2" class="text-center">TOTAL AKUMULASI</td>
                <td class="text-right">Rp {{ number_format($totalInc, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalExp, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalInc - $totalExp, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($running, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p style="font-style: italic; font-size: 9px; margin-top: 20px;">* Laporan ini dihasilkan secara otomatis oleh sistem eBimbel.</p>
</body>
</html>
