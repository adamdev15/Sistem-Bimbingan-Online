<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $salary->tutor->nama }} - {{ $salary->periode }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        .container { padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 2px 0; font-size: 10px; color: #666; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .label { font-weight: bold; width: 120px; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th { background: #f5f5f5; border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 11px; text-transform: uppercase; }
        .items-table td { border: 1px solid #ddd; padding: 8px; font-size: 11px; }
        .text-right { text-align: right; }
        
        .total-section { float: right; width: 250px; }
        .total-row { display: table; width: 100%; margin-bottom: 5px; }
        .total-label { display: table-cell; font-weight: bold; }
        .total-value { display: table-cell; text-align: right; font-weight: bold; }
        
        .footer { margin-top: 50px; }
        .signature-table { width: 100%; text-align: center; }
        .signature-space { height: 60px; }
        
        .status-stamp { 
            position: absolute; top: 100px; right: 50px; 
            border: 3px solid #ccc; padding: 10px 20px; 
            font-size: 20px; font-weight: bold; text-transform: uppercase;
            transform: rotate(-15deg); opacity: 0.3;
        }
        .status-dibayar { border-color: #059669; color: #059669; }
        .status-pending { border-color: #d97706; color: #d97706; }
    </style>
</head>
<body>
    <div class="container">
        @if($salary->status === 'dibayar' || $salary->status === 'diterima')
            <div class="status-stamp status-dibayar">LUNAS</div>
        @else
            <div class="status-stamp status-pending">PENDING</div>
        @endif

        <div class="header">
            <h1>Slip Gaji Tutor</h1>
            <p>{{ $salary->tutor->cabang->nama_cabang ?? 'Bimbel Jarimatrik' }}</p>
            <p>{{ $salary->tutor->cabang->alamat ?? '' }}</p>
        </div>

        <table class="info-table">
            <tr>
                <td class="label">Nama Tutor</td>
                <td>: {{ $salary->tutor->nama }}</td>
                <td class="label">Periode</td>
                <td>: {{ $salary->periode }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td>: {{ $salary->tutor->jabatan ?? 'Tutor' }}</td>
                <td class="label">Tanggal Cetak</td>
                <td>: {{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Deskripsi Item</th>
                    <th class="text-right" style="width: 60px;">Jumlah</th>
                    <th class="text-right" style="width: 100px;">Tarif Persesi</th>
                    <th class="text-right" style="width: 120px;">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salary->items as $item)
                    <tr>
                        <td>{{ $item->nama_item }}</td>
                        <td class="text-right">{{ $item->qty }}</td>
                        <td class="text-right">{{ number_format($item->tarif, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                @if($salary->bonus > 0)
                    <tr>
                        <td colspan="3">Bonus Tambahan</td>
                        <td class="text-right">{{ number_format($salary->bonus, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if($salary->lain_lainnya > 0)
                    <tr>
                        <td colspan="3">Lain-lainnya</td>
                        <td class="text-right">{{ number_format($salary->lain_lainnya, 0, ',', '.') }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="total-section">
            <div class="total-row" style="border-top: 2px solid #444; padding-top: 10px;">
                <div class="total-label" style="font-size: 14px;">TOTAL GAJI</div>
                <div class="total-value" style="font-size: 14px;">Rp {{ number_format($salary->total_gaji, 0, ',', '.') }}</div>
            </div>
        </div>
        
        <p style="font-size: 9px; color: #999; font-style: italic;">
            * Slip gaji ini dihasilkan secara otomatis oleh sistem gaji tutor Bimbel Jarimatrik.
        </p>
    </div>
</body>
</html>
