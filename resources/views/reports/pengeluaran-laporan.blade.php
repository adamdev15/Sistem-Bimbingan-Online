<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Operasional Pengeluaran - {{ $cabang->nama_cabang }}</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', sans-serif; font-size: 11px; line-height: 1.4; color: #333; }
        
        .header { width: 100%; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header table { width: 100%; border: none; }
        .header .logo { width: 80px; text-align: left; }
        .header .title { text-align: center; }
        .header h1 { font-size: 16px; margin: 0; color: #000; text-transform: uppercase; }
        .header h2 { font-size: 14px; margin: 4px 0; color: #444; }
        
        .report-info { margin-bottom: 15px; }
        .report-info table { width: 100%; border-collapse: collapse; }
        .report-info td { padding: 2px 0; }
        
        .main-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .main-table th { border: 1px solid #000; padding: 10px 5px; background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .main-table td { border: 1px solid #000; padding: 8px 5px; vertical-align: top; }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .total-row { background-color: #f9f9f9; font-weight: bold; }
        .total-label { font-size: 12px; }
        
        .footer-note { margin-top: 30px; font-size: 9px; text-align: right; font-style: italic; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="logo">
                    @if(file_exists(public_path('image/logo-bimbel.png')))
                        <img src="{{ public_path('image/logo-bimbel.png') }}" style="height: 60px;">
                    @else
                       <div style="width: 60px; height: 60px; border: 1px solid #ccc; line-height: 60px; text-align: center;">LOGO</div>
                    @endif
                </td>
                <td class="title">
                    <h1>Laporan Operasional Pengeluaran</h1>
                    <h2>Bimbel Jarimatrik Tegal - {{ $cabang->nama_cabang }}</h2>
                </td>
                <td class="logo" style="width: 80px;"></td> <!-- Spacer -->
            </tr>
        </table>
    </div>

    <div class="report-info">
        <table>
            <tr>
                <td width="80">Periode</td>
                <td width="10">:</td>
                <td>{{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</td>
            </tr>
            <tr>
                <td>Dicetak pada</td>
                <td>:</td>
                <td>{{ now()->translatedFormat('d F Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <table class="main-table">
        <thead>
            <tr>
                <th width="12%">Tgl.</th>
                <th width="43%">KETERANGAN TRANSAKSI</th>
                <th width="8%">Hal</th>
                <th width="17%">Debet (Nominal)</th>
                <th width="20%">Credit (Total)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $item)
                <tr>
                    <td class="text-center">{{ \Carbon\Carbon::parse($item['tanggal'])->format('d.m.y') }}</td>
                    <td>{{ $item['keterangan'] }}</td>
                    <td class="text-center">{{ $item['hal'] }}</td>
                    <td class="text-right">{{ number_format($item['nominal'], 0, ',', '.') }}</td>
                    <td class="text-right font-bold">{{ number_format($item['total'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px;">Belum ada data pengeluaran pada periode ini.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="4" class="text-right total-label">TOTAL PENGELUARAN AKHIR</td>
                <td class="text-right total-label">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer-note">
        Dokumen ini dihasilkan secara otomatis oleh Sistem Manajemen Bimbel Jarimatrik Tegal.
    </div>
</body>
</html>
