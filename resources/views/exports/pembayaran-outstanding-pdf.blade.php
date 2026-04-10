<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tagihan outstanding</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
        h1 { font-size: 15px; margin: 0 0 6px; }
        .muted { color: #555; font-size: 9px; margin-bottom: 10px; }
        .summary { margin-bottom: 10px; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ccc; padding: 3px 5px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-size: 8px; text-transform: uppercase; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .insight { background: #fff7ed; padding: 8px; border: 1px solid #fed7aa; margin-bottom: 12px; font-size: 9px; line-height: 1.45; }
    </style>
</head>
<body>
    <h1>Tagihan outstanding (belum lunas)</h1>
    <p class="muted">
        Dicetak {{ $generated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }} · {{ $filter_label }}
    </p>
    <table>
        <thead>
            <tr>
                <th>Ref</th>
                <th>Siswa</th>
                <th>Cabang</th>
                <th>Item</th>
                <th class="num">Nominal</th>
                <th>Terbit</th>
                <th>Jatuh tempo</th>
                <th>Aging</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @php
                    $p = $row['payment'];
                @endphp
                <tr>
                    <td>INV-{{ str_pad((string) $p->id, 5, '0', STR_PAD_LEFT) }}</td>
                    <td>{{ optional($p->siswa)->nama }}</td>
                    <td>{{ optional(optional($p->siswa)->cabang)->nama_cabang }}</td>
                    <td>{{ optional($p->fee)->nama_biaya }}</td>
                    <td class="num">{{ number_format((int) round((float) $p->nominal), 0, ',', '.') }}</td>
                    <td>{{ optional($p->tanggal_bayar)?->format('d/m/Y') }}</td>
                    <td>{{ optional($p->due_date)?->format('d/m/Y') ?? '—' }}</td>
                    <td>{{ $row['aging_label'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
