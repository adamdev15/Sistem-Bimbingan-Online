<!DOCTYPE html>
<html>
<head>
    <title>Laporan Mitra - {{ $month }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #1e293b; }
        .header p { margin: 5px 0; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8fafc; color: #475569; font-weight: bold; border: 1px solid #cbd5e1; padding: 10px; text-transform: uppercase; font-size: 9px; }
        td { border: 1px solid #cbd5e1; padding: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .section-header { background-color: #f1f5f9; font-weight: bold; font-size: 8px; text-transform: uppercase; }
        .total-row { background-color: #f8fafc; font-weight: bold; }
        .signature-section { margin-top: 40px; width: 100%; }
        .signature-section table { border: none; }
        .signature-section td { border: none; width: 50%; text-align: center; }
        .signature-space { height: 70px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN ANALISA MITRA</h2>
        <p>{{ $cabang->nama_cabang }}</p>
        <p>Periode: {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">NO</th>
                <th>KETERANGAN</th>
                <th class="text-right">PEMASUKAN</th>
                <th class="text-right">PENGELUARAN</th>
                <th class="text-right">SALDO</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($incomeBreakdown as $income)
                <tr>
                    <td class="text-center">{{ $no++ }}.</td>
                    <td>{{ $income->nama_biaya }}</td>
                    <td class="text-right" style="color: #059669; font-weight: bold;">{{ number_format($income->total, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #94a3b8;">—</td>
                    <td class="text-right" style="color: #94a3b8;">—</td>
                </tr>
            @endforeach

            @foreach ($expenseBreakdown as $expense)
                <tr>
                    <td class="text-center">{{ $no++ }}.</td>
                    <td>{{ $expense->nama_kategori }}</td>
                    <td class="text-right" style="color: #94a3b8;">—</td>
                    <td class="text-right" style="color: #dc2626; font-weight: bold;">{{ number_format($expense->total, 0, ',', '.') }}</td>
                    <td class="text-right" style="color: #94a3b8;">—</td>
                </tr>
            @endforeach

            <tr>
                <td class="text-center">{{ $no++ }}.</td>
                <td>Honor Guru (Gaji)</td>
                <td class="text-right" style="color: #94a3b8;">—</td>
                <td class="text-right" style="color: #dc2626; font-weight: bold;">{{ number_format($totalSalaries, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #94a3b8;">—</td>
            </tr>

            <tr class="total-row">
                <td colspan="2" class="text-center">JUMLAH</td>
                <td class="text-right">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalExpenses, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
            </tr>

            <tr class="section-header">
                <td colspan="5">BAGI HASIL (BERDASARKAN LABA BERSIH)</td>
            </tr>
            <tr>
                <td class="text-center">a.</td>
                <td>
                    <strong>Investor</strong> 
                    <small>({{ number_format($shares['investor_pct'], 1) }}% x {{ number_format($netProfit, 0, ',', '.') }})</small>
                </td>
                <td class="text-right" style="color: #94a3b8;">—</td>
                <td class="text-right font-bold">{{ number_format($shares['investor_amount'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #94a3b8;">—</td>
            </tr>
            <tr>
                <td class="text-center">b.</td>
                <td>
                    <strong>Pusat</strong> 
                    <small>({{ number_format($shares['pusat_pct'], 1) }}% x {{ number_format($netProfit, 0, ',', '.') }})</small>
                </td>
                <td class="text-right" style="color: #94a3b8;">—</td>
                <td class="text-right font-bold">{{ number_format($shares['pusat_amount'], 0, ',', '.') }}</td>
                <td class="text-right" style="color: #94a3b8;">—</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <table width="100%">
            <tr>
                <td>
                    <p>Investor Cabang</p>
                    <div class="signature-space"></div>
                    <p>__________________________</p>
                </td>
                <td>
                    <p>Tegal, {{ now()->translatedFormat('d F Y') }}</p>
                    <p>Kepala Pusat</p>
                    <div class="signature-space"></div>
                    <p><strong>Dr. Nurhayati Sueb</strong></p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
