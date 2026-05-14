<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Gaji Tutor — Bimbel Jarimatrik</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e293b; margin: 0; padding: 0; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 20px; }
        .header-title { font-size: 18px; font-weight: bold; color: #0f172a; text-transform: uppercase; }
        .header-sub { font-size: 11px; color: #64748b; margin-top: 2px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; vertical-align: middle; }
        th { background: #f8fafc; font-size: 8px; font-weight: bold; color: #475569; text-transform: uppercase; letter-spacing: 0.025em; }
        
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .font-bold { font-weight: bold; }
        .text-emerald { color: #059669; }
        .bg-slate { background: #f1f5f9; }
        
        .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 7px; font-weight: bold; text-transform: uppercase; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-dibayar { background: #dcfce7; color: #166534; }
        .badge-diterima { background: #dbeafe; color: #1e40af; }

        .summary-box { width: 250px; margin-bottom: 20px; }
        .insight-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px; border-radius: 8px; margin-bottom: 20px; line-height: 1.4; }
    </style>
</head>
<body>
    <div class="header">
        <table style="border: none; margin-bottom: 0;">
            <tr style="border: none;">
                <td style="border: none; padding: 0; width: 60%;">
                    <div class="header-title">Laporan Gaji Tutor</div>
                    <div class="header-sub">Bimbel Jarimatrik Tegal</div>
                </td>
                <td style="border: none; padding: 0; text-align: right;">
                    <div style="font-size: 10px; color: #64748b;">Dicetak pada: {{ $generated_at->format('d/m/Y H:i') }}</div>
                    <div style="font-size: 11px; font-weight: bold; color: #1e293b; margin-top: 4px;">{{ $filter_label }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-box">
        <table>
            <thead>
                <tr><th colspan="2" style="background: #0f172a; color: #ffffff;">Ringkasan Laporan</th></tr>
            </thead>
            <tbody>
                <tr>
                    <td style="color: #64748b;">Total Data Masuk</td>
                    <td class="num font-bold">{{ $entri_count }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="font-size: 11px; font-weight: bold; color: #0f172a; margin-bottom: 10px;">Detail Rincian Gaji</div>
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 15%;">Periode</th>
                <th style="width: 12%;">Nama Tutor</th>
                @if ($is_super_admin)
                    <th style="width: 10%;">Cabang</th>
                @endif
                <th class="num">Full (Rp)</th>
                <th class="num">P-S (Rp)</th>
                <th class="num">S-S (Rp)</th>
                <th class="num">MLM (Rp)</th>
                <th class="num">Bonus</th>
                <th class="num">Lainnya</th>
                <th class="num" style="width: 10%;">Total Gaji</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 12%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($detail_rows as $s)
                <tr>
                    <td style="color: #94a3b8;">#{{ $s->id }}</td>
                    <td class="font-bold">{{ $s->periode }}</td>
                    <td>{{ optional($s->tutor)->nama }}</td>
                    @if ($is_super_admin)
                        <td>{{ optional(optional($s->tutor)->cabang)->nama_cabang ?? '—' }}</td>
                    @endif
                    <td class="num">Rp {{ number_format((float) $s->count_full, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format((float) $s->count_pagi_siang, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format((float) $s->count_siang_sore, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format((float) $s->count_kelas_malam, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format((float) $s->bonus, 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format((float) $s->lain_lainnya, 0, ',', '.') }}</td>
                    <td class="num font-bold text-emerald">Rp {{ number_format((float) $s->total_gaji, 0, ',', '.') }}</td>
                    <td style="text-align: center;">
                        <span class="badge badge-{{ $s->status }}">{{ $s->status }}</span>
                    </td>
                    <td style="font-size: 8px; color: #64748b;">{{ $s->catatan ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-slate font-bold">
                <td colspan="{{ $is_super_admin ? 6 : 5 }}"></td>
                <td colspan="4" style="text-align: right; text-transform: uppercase;">Total Gaji Keseluruhan</td>
                <td class="num text-emerald">Rp {{ number_format((float) $total_gaji, 0, ',', '.') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px; font-style: italic; color: #94a3b8; font-size: 8px; text-align: center;">
        Laporan ini digenerate secara otomatis oleh Sistem Manajemen Bimbel Jarimatrik.
    </div>
</body>
</html>
