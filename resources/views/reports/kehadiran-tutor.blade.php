<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kehadiran Tutor - {{ $period }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #1e293b; text-transform: uppercase; }
        .header p { margin: 5px 0; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f8fafc; color: #475569; font-weight: bold; border: 1px solid #cbd5e1; padding: 8px; text-transform: uppercase; font-size: 9px; }
        td { border: 1px solid #cbd5e1; padding: 8px; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .branch-header { background-color: #f1f5f9; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN KEHADIRAN TUTOR</h2>
        <p>Cabang: {{ $cabangName }}</p>
        <p>Periode: {{ $period }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">NO</th>
                <th>NAMA TUTOR</th>
                <th class="text-center">FULL DAY</th>
                <th class="text-center">PAGI-SIANG</th>
                <th class="text-center">SIANG-SORE</th>
                <th class="text-center">TOTAL SESI</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($data as $cabangId => $tutors)
                @foreach ($tutors as $tutorId => $row)
                    <tr>
                        <td class="text-center">{{ $no++ }}.</td>
                        <td>
                            <div class="font-bold">{{ $row['tutor'] }}</div>
                            <small style="color: #64748b;">{{ $row['cabang'] }}</small>
                        </td>
                        <td class="text-center">{{ $row['full'] }}x</td>
                        <td class="text-center">{{ $row['pagi_siang'] }}x</td>
                        <td class="text-center">{{ $row['siang_sore'] }}x</td>
                        <td class="text-center font-bold">
                            {{ $row['full'] + ($row['pagi_siang'] + $row['siang_sore']) * 0.5 }}
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right; padding-right: 50px;">
        <p>Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</p>
        <div style="height: 60px;"></div>
        <p><strong>Admin Pusat Jarimatrik</strong></p>
    </div>
</body>
</html>
