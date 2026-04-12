<!DOCTYPE html>
<html>
<head>
    <title>Laporan Bulanan - {{ $month }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #1e293b; }
        .header p { margin: 5px 0; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8fafc; color: #475569; font-weight: bold; border: 1px solid #cbd5e1; padding: 10px; text-transform: uppercase; font-size: 9px; letter-spacing: 1px; }
        td { border: 1px solid #cbd5e1; padding: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .total-row { background-color: #f1f5f9; font-weight: bold; color: #000; }
        .signature-section { margin-top: 50px; width: 100%; }
        .signature-section table { border: none; }
        .signature-section td { border: none; width: 50%; text-align: center; }
        .signature-space { height: 80px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN BULANAN CABANG</h2>
        <p>{{ $cabang->nama_cabang }}</p>
        <p>Periode: {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="40">NO</th>
                <th>KETERANGAN</th>
                <th class="text-right">PEMASUKAN</th>
                <th class="text-right">PENGELUARAN</th>
                <th class="text-right">SALDO</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $no = 1; 
                $running = 0;
            @endphp

            @foreach ($rekapIncome as $item)
                @php $running += $item['nominal']; @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}.</td>
                    <td>{{ $item['keterangan'] }}</td>
                    <td class="text-right font-bold" style="color: #059669;">
                        {{ $item['nominal'] > 0 ? 'Rp ' . number_format($item['nominal'], 0, ',', '.') : '—' }}
                    </td>
                    <td class="text-right" style="color: #94a3b8;">—</td>
                    <td class="text-right">Rp {{ number_format($running, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            @php $running -= $totalOperasional; @endphp
            <tr>
                <td class="text-center">{{ $no++ }}.</td>
                <td>Operasional</td>
                <td class="text-right" style="color: #94a3b8;">—</td>
                <td class="text-right font-bold" style="color: #dc2626;">
                    {{ $totalOperasional > 0 ? 'Rp ' . number_format($totalOperasional, 0, ',', '.') : '—' }}
                </td>
                <td class="text-right font-bold">Rp {{ number_format($running, 0, ',', '.') }}</td>
            </tr>

            <tr class="total-row">
                <td colspan="2" class="text-center">JUMLAH TOTAL</td>
                <td class="text-right">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalOperasional, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($running, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <table width="100%">
            <tr>
                <td>
                    <p>Admin Cabang</p>
                    <div class="signature-space"></div>
                    <p>__________________________</p>
                </td>
                <td>
                    <p>Tegal, {{ now()->translatedFormat('d F Y') }}</p>
                    <p>Kepala Pusat Bimbel</p>
                    <div class="signature-space"></div>
                    <p><strong>Dr. Nurhayati Sueb</strong></p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
