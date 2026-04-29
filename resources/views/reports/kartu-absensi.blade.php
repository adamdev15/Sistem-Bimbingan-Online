<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kartu Absensi - {{ $siswa->nama }}</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'DejaVu Sans', 'Arial', sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header table { width: 100%; border: none; }
        .header .logo { width: 70px; text-align: left; }
        .header .title { text-align: center; }
        .header h1 { font-size: 18px; margin: 0; color: #000; }
        .header h2 { font-size: 14px; margin: 2px 0; color: #444; }
        
        .info-section { margin-bottom: 15px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        .info-label { font-weight: bold; width: 100px; }
        .info-dots { width: 10px; }
        
        .card-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .card-table th { border: 1px solid #000; padding: 8px; background-color: #f5f5f5; font-weight: bold; text-align: center; }
        .card-table td { border: 1px solid #000; padding: 5px; vertical-align: top; }
        
        .month-cell { text-align: center; vertical-align: middle !important; font-weight: bold; font-size: 12px; }
        .payment-cell { text-align: center; vertical-align: middle !important; font-size: 10px; }
        
        .date-container { width: 100%; }
        .date-grid { width: 100%; border-collapse: collapse; }
        .date-grid td { border: 1px solid #ccc; width: 12.5%; height: 28px; text-align: center; position: relative; font-size: 9px; color: #777; padding: 0; }
        
        .present { background-color: #e8f5e9; font-weight: bold; color: #000 !important; }
        .present-mark { position: absolute; top: 0; left: 0; width: 100%; height: 100%; text-align: center; line-height: 28px; font-size: 18px; font-weight: bold; color: #c00; opacity: 0.7; }
        
        .footer-note { margin-top: 20px; font-style: italic; font-size: 9px; text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="logo">
                    @if(file_exists(public_path('image/logo-bimbel.png')))
                        <img src="{{ public_path('image/logo-bimbel.png') }}" style="height: 60px;">
                    @endif
                </td>
                <td class="title">
                    <h1>KARTU ABSENSI SISWA</h1>
                    <h2>BIMBEL JARIMATRIK TEGAL</h2>
                </td>
                <td class="logo"></td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="info-label">Nama Lengkap</td>
                <td class="info-dots">:</td>
                <td style="border-bottom: 1px dotted #333;">{{ $siswa->nama }}</td>
                <td width="20"></td>
                <td class="info-label" style="width: 150px;">Jatuh Tempo tiap tgl</td>
                <td class="info-dots">:</td>
                <td style="border-bottom: 1px dotted #333;">{{ $jatuhTempoTgl }}</td>
            </tr>
            <tr>
                <td class="info-label">Materi Les</td>
                <td class="info-dots">:</td>
                <td style="border-bottom: 1px dotted #333;">{{ $siswa->materiLes->nama_materi ?? '-' }}</td>
                <td></td>
                <td class="info-label">No HP</td>
                <td class="info-dots">:</td>
                <td style="border-bottom: 1px dotted #333;">{{ $siswa->no_hp }}</td>
            </tr>
        </table>
    </div>

    <table class="card-table">
        <thead>
            <tr>
                <th width="12%">Bulan</th>
                <th width="18%">Pembayaran Spp</th>
                <th width="70%">Tanggal Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($monthsData as $data)
                @php
                    $payment = $data['payment'];
                    $sppStatus = '';
                    if ($payment) {
                        if ($payment->status === 'lunas') {
                            $sppStatus = 'LUNAS (' . number_format($payment->nominal, 0, ',', '.') . ')';
                        } else {
                            $sppStatus = 'BELUM LUNAS';
                        }
                    }
                @endphp
                <tr>
                    <td height="110" class="month-cell">{{ $data['monthName'] }}</td>
                    <td class="payment-cell">{{ $sppStatus }}</td>
                    <td>
                        <table class="date-grid">
                            @for ($row = 0; $row < 4; $row++)
                                <tr>
                                    @for ($col = 1; $col <= 8; $col++)
                                        @php $day = ($row * 8) + $col; @endphp
                                        @if ($day <= 31)
                                            @php $isPresent = in_array($day, $data['presenceDays']); @endphp
                                            <td class="{{ $isPresent ? 'present' : '' }}">
                                                {{ $day }}
                                                @if ($isPresent)
                                                    <span class="present-mark">✔</span>
                                                @endif
                                            </td>
                                        @else
                                            <td></td>
                                        @endif
                                    @endfor
                                </tr>
                            @endfor
                        </table>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-note">
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}
    </div>
</body>
</html>
