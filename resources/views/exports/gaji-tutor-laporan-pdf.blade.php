<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan gaji tutor</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
        h1 { font-size: 14px; margin: 0 0 4px; }
        .muted { color: #555; font-size: 8px; margin-bottom: 10px; }
        .insight { background: #f1f5f9; border: 1px solid #e2e8f0; padding: 6px; margin-bottom: 10px; font-size: 8px; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #ccc; padding: 3px 5px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-size: 7px; text-transform: uppercase; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        h2 { font-size: 10px; margin: 10px 0 4px; }
    </style>
</head>
<body>
    <h1>Laporan gaji tutor</h1>
    <p class="muted">{{ $generated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }} · {{ $filter_label }}</p>

    <table>
        <tr><th colspan="2">Ringkasan</th></tr>
        <tr><td>Total entri</td><td class="num">{{ $entri_count }}</td></tr>
        <tr><td>Total jam mengajar</td><td class="num">{{ $total_jam }}</td></tr>
        <tr><td>Total nominal gaji</td><td class="num">Rp {{ number_format((int) round($total_gaji), 0, ',', '.') }}</td></tr>
    </table>

    <div class="insight">
        <strong>Insight</strong><br>
        {{ $insight_aktif }}<br>
        {{ $insight_biaya }}@if($insight_top_gaji !== '')<br>{{ $insight_top_gaji }}@endif
    </div>

    <h2>Agregat per tutor</h2>
    <table>
        <thead>
            <tr>
                <th>Tutor</th>
                @if ($is_super_admin)
                    <th>Cabang</th>
                @endif
                <th class="num">Jam</th>
                <th class="num">Gaji</th>
                <th class="num">Entri</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($per_tutor as $row)
                <tr>
                    <td>{{ $row->tutor_nama }}</td>
                    @if ($is_super_admin)
                        <td>{{ $row->nama_cabang ?? '—' }}</td>
                    @endif
                    <td class="num">{{ (int) $row->total_jam }}</td>
                    <td class="num">{{ number_format((int) round((float) $row->total_gaji), 0, ',', '.') }}</td>
                    <td class="num">{{ (int) $row->entri_count }}</td>
                </tr>
            @empty
                <tr><td colspan="{{ $is_super_admin ? 5 : 4 }}">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Per periode</h2>
    <table>
        <thead>
            <tr>
                <th>Periode</th>
                <th class="num">Jam</th>
                <th class="num">Gaji</th>
                <th class="num">Entri</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($per_periode as $row)
                <tr>
                    <td>{{ $row->periode }}</td>
                    <td class="num">{{ (int) $row->total_jam }}</td>
                    <td class="num">{{ number_format((int) round((float) $row->total_gaji), 0, ',', '.') }}</td>
                    <td class="num">{{ (int) $row->entri_count }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detail entri</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Periode</th>
                <th>Tutor</th>
                @if ($is_super_admin)
                    <th>Cabang</th>
                @endif
                <th class="num">Jam</th>
                <th class="num">Gaji</th>
                <th>Status</th>
                <th>Dicatat</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detail_rows as $s)
                <tr>
                    <td>{{ $s->id }}</td>
                    <td>{{ $s->periode }}</td>
                    <td>{{ optional($s->tutor)->nama }}</td>
                    @if ($is_super_admin)
                        <td>{{ optional(optional($s->tutor)->cabang)->nama_cabang ?? '—' }}</td>
                    @endif
                    <td class="num">{{ (int) $s->total_jam }}</td>
                    <td class="num">{{ number_format((int) round((float) $s->total_gaji), 0, ',', '.') }}</td>
                    <td>{{ $s->status }}</td>
                    <td>{{ optional($s->creator)->name ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
