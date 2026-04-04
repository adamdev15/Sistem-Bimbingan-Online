<?php

namespace App\Exports;

use Carbon\CarbonInterface;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GajiTutorLaporanExport implements WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(private readonly array $payload) {}

    public function sheets(): array
    {
        return [
            new ArrayExportSheet($this->sheetRingkasan(), 'Ringkasan'),
            new ArrayExportSheet($this->sheetPerTutor(), 'Per_tutor'),
            new ArrayExportSheet($this->sheetPerPeriode(), 'Per_periode'),
            new ArrayExportSheet($this->sheetDetail(), 'Detail_entri'),
        ];
    }

    private function sheetRingkasan(): array
    {
        /** @var CarbonInterface $at */
        $at = $this->payload['generated_at'];

        $rows = [
            ['Laporan gaji tutor eBimbel'],
            ['Dicetak', $at->timezone(config('app.timezone'))->format('d/m/Y H:i')],
            ['Filter', $this->payload['filter_label']],
            [],
            ['Total entri', (int) ($this->payload['entri_count'] ?? 0)],
            ['Total jam mengajar (jumlah)', (int) ($this->payload['total_jam'] ?? 0)],
            ['Total nominal gaji (Rp)', (int) round((float) ($this->payload['total_gaji'] ?? 0))],
            [],
            ['Insight'],
            [$this->payload['insight_aktif']],
            [$this->payload['insight_biaya']],
        ];
        if (($this->payload['insight_top_gaji'] ?? '') !== '') {
            $rows[] = [$this->payload['insight_top_gaji']];
        }

        return $rows;
    }

    private function sheetPerTutor(): array
    {
        $isSuper = (bool) ($this->payload['is_super_admin'] ?? false);
        $head = $isSuper
            ? ['Tutor', 'Cabang', 'Total jam', 'Total gaji (Rp)', 'Jumlah entri']
            : ['Tutor', 'Total jam', 'Total gaji (Rp)', 'Jumlah entri'];
        $rows = [$head];
        foreach ($this->payload['per_tutor'] as $row) {
            if ($isSuper) {
                $rows[] = [
                    $row->tutor_nama,
                    $row->nama_cabang ?? '—',
                    (int) $row->total_jam,
                    (int) round((float) $row->total_gaji),
                    (int) $row->entri_count,
                ];
            } else {
                $rows[] = [
                    $row->tutor_nama,
                    (int) $row->total_jam,
                    (int) round((float) $row->total_gaji),
                    (int) $row->entri_count,
                ];
            }
        }

        return $rows;
    }

    private function sheetPerPeriode(): array
    {
        $rows = [['Periode', 'Total jam', 'Total gaji (Rp)', 'Jumlah entri']];
        foreach ($this->payload['per_periode'] as $row) {
            $rows[] = [
                $row->periode,
                (int) $row->total_jam,
                (int) round((float) $row->total_gaji),
                (int) $row->entri_count,
            ];
        }

        return $rows;
    }

    private function sheetDetail(): array
    {
        $isSuper = (bool) ($this->payload['is_super_admin'] ?? false);
        $head = $isSuper
            ? ['ID', 'Periode', 'Tutor', 'Cabang', 'Jam', 'Gaji (Rp)', 'Status', 'Dicatat oleh']
            : ['ID', 'Periode', 'Tutor', 'Jam', 'Gaji (Rp)', 'Status', 'Dicatat oleh'];
        $rows = [$head];
        foreach ($this->payload['detail_rows'] as $s) {
            $tutor = $s->tutor;
            $cab = $tutor?->cabang?->nama_cabang ?? '—';
            $base = [
                $s->id,
                $s->periode,
                $tutor?->nama ?? '—',
            ];
            if ($isSuper) {
                $base[] = $cab;
            }
            $base[] = (int) $s->total_jam;
            $base[] = (int) round((float) $s->total_gaji);
            $base[] = $s->status;
            $base[] = $s->creator?->name ?? '—';
            $rows[] = $base;
        }

        return $rows;
    }
}
