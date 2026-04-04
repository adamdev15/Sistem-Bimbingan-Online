<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Ringkasan pembayaran</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        h1 { font-size: 15px; margin: 0 0 6px; }
        .muted { color: #555; font-size: 9px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; vertical-align: top; }
        th { background: #f0f0f0; font-size: 9px; text-transform: uppercase; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .insight { background: #f8fafc; padding: 8px; border: 1px solid #e2e8f0; margin-bottom: 12px; font-size: 9px; line-height: 1.45; }
        .grid-2 { width: 100%; }
        .grid-2 td { width: 50%; border: none; padding: 0 8px 0 0; vertical-align: top; }
    </style>
</head>
<body>
    <h1>Laporan ringkasan pembayaran</h1>
    <p class="muted">
        Dicetak {{ $generated_at->timezone(config('app.timezone'))->format('d/m/Y H:i') }} · {{ $filter_label }}
    </p>

    @php
        $s = $summary;
    @endphp
    <table>
        <tr><th colspan="2">Ringkasan agregat (sesuai filter)</th></tr>
        <tr><td>Total pemasukan tercatat (nominal terbit)</td><td class="num">Rp {{ number_format((int) ($s['total'] ?? 0), 0, ',', '.') }}</td></tr>
        <tr><td>Sudah lunas</td><td class="num">Rp {{ number_format((int) ($s['paid'] ?? 0), 0, ',', '.') }}</td></tr>
        <tr><td>Outstanding / belum lunas</td><td class="num">Rp {{ number_format((int) ($s['outstanding'] ?? 0), 0, ',', '.') }}</td></tr>
        <tr><td>Jumlah transaksi lunas</td><td class="num">{{ (int) ($s['lunas_count'] ?? 0) }}</td></tr>
        <tr><td>Jumlah transaksi belum lunas</td><td class="num">{{ (int) ($s['belum_count'] ?? 0) }}</td></tr>
    </table>

    <div class="insight">
        <strong>Insight</strong><br>
        {{ $insight_cabang }}<br>
        {{ $insight_siswa }}
    </div>

    <table class="grid-2"><tr>
        <td>
            <strong>Per cabang</strong>
            <table>
                <thead>
                    <tr>
                        <th>Cabang</th>
                        <th class="num">Total terbit</th>
                        <th class="num">Lunas</th>
                        <th class="num">Belum</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($per_cabang as $row)
                        <tr>
                            <td>{{ $row->nama_cabang }}</td>
                            <td class="num">{{ number_format((int) $row->total_terbit, 0, ',', '.') }}</td>
                            <td class="num">{{ number_format((int) $row->nominal_lunas, 0, ',', '.') }}</td>
                            <td class="num">{{ number_format((int) $row->nominal_belum, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Tidak ada baris.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </td>
        <td>
            <strong>Per periode (bulan terbit)</strong>
            <table>
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th class="num">Total terbit</th>
                        <th class="num">Lunas</th>
                        <th class="num">Belum</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($per_periode as $row)
                        <tr>
                            <td>{{ $row->periode_label }}</td>
                            <td class="num">{{ number_format((int) $row->total_terbit, 0, ',', '.') }}</td>
                            <td class="num">{{ number_format((int) $row->nominal_lunas, 0, ',', '.') }}</td>
                            <td class="num">{{ number_format((int) $row->nominal_belum, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Tidak ada baris.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </td>
    </tr></table>

    <strong>Top siswa belum lunas (agregat, max 50 baris)</strong>
    <table>
        <thead>
            <tr>
                <th>Siswa</th>
                <th>Cabang</th>
                <th class="num">Jumlah tagihan</th>
                <th class="num">Total nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($siswa_belum_bayar as $row)
                <tr>
                    <td>{{ $row['nama'] }}</td>
                    <td>{{ $row['cabang'] }}</td>
                    <td class="num">{{ $row['jumlah_tagihan'] }}</td>
                    <td class="num">{{ number_format((int) $row['total_nominal'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
