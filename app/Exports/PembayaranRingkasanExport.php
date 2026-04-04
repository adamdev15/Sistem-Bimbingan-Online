<?php

namespace App\Exports;

use Carbon\CarbonInterface;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PembayaranRingkasanExport implements WithMultipleSheets
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(private readonly array $payload) {}

    public function sheets(): array
    {
        $sheets = [];
        $sheets[] = new ArrayExportSheet($this->buildRingkasan(), 'Ringkasan');
        $sheets[] = new ArrayExportSheet($this->buildPerCabang(), 'Per_cabang');
        $sheets[] = new ArrayExportSheet($this->buildPerPeriode(), 'Per_periode');
        $sheets[] = new ArrayExportSheet($this->buildSiswaBelum(), 'Siswa_belum_bayar');

        return $sheets;
    }

    private function buildRingkasan(): array
    {
        /** @var CarbonInterface $at */
        $at = $this->payload['generated_at'];
        $s = $this->payload['summary'];

        return [
            ['Laporan ringkasan pembayaran eBimbel'],
            ['Dicetak', $at->timezone(config('app.timezone'))->format('d/m/Y H:i')],
            ['Filter', $this->payload['filter_label']],
            [],
            ['Total pemasukan tercatat (nominal terbit)', (int) ($s['total'] ?? 0)],
            ['Sudah lunas (Rp)', (int) ($s['paid'] ?? 0)],
            ['Outstanding / belum lunas (Rp)', (int) ($s['outstanding'] ?? 0)],
            ['Jumlah transaksi lunas', (int) ($s['lunas_count'] ?? 0)],
            ['Jumlah transaksi belum lunas', (int) ($s['belum_count'] ?? 0)],
            [],
            ['Insight'],
            [$this->payload['insight_cabang']],
            [$this->payload['insight_siswa']],
        ];
    }

    private function buildPerCabang(): array
    {
        $rows = [['Cabang', 'Total terbit (Rp)', 'Lunas (Rp)', 'Belum (Rp)', 'Trx lunas', 'Trx belum']];
        foreach ($this->payload['per_cabang'] as $row) {
            $rows[] = [
                $row->nama_cabang,
                (int) $row->total_terbit,
                (int) $row->nominal_lunas,
                (int) $row->nominal_belum,
                (int) $row->trx_lunas,
                (int) $row->trx_belum,
            ];
        }

        return $rows;
    }

    private function buildPerPeriode(): array
    {
        $rows = [['Periode (Y-m)', 'Total terbit (Rp)', 'Lunas (Rp)', 'Belum (Rp)']];
        foreach ($this->payload['per_periode'] as $row) {
            $rows[] = [
                $row->periode_label,
                (int) $row->total_terbit,
                (int) $row->nominal_lunas,
                (int) $row->nominal_belum,
            ];
        }

        return $rows;
    }

    private function buildSiswaBelum(): array
    {
        $rows = [['Siswa', 'Cabang', 'Jumlah tagihan belum lunas', 'Total nominal (Rp)']];
        foreach ($this->payload['siswa_belum_bayar'] as $row) {
            $rows[] = [$row['nama'], $row['cabang'], $row['jumlah_tagihan'], (int) $row['total_nominal']];
        }

        return $rows;
    }
}
