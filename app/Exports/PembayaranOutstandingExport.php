<?php

namespace App\Exports;

use App\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class PembayaranOutstandingExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
     * @param  Collection<int, array{payment: Payment, aging_hari: int|null, aging_label: string}>  $rows
     */
    public function __construct(
        private readonly Collection $rows,
    ) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'Referensi',
            'Siswa',
            'Cabang',
            'Item biaya',
            'Nominal (Rp)',
            'Tanggal terbit',
            'Jatuh tempo',
            'Aging (hari)',
            'Keterangan aging',
        ];
    }

    /**
     * @param  array{payment: Payment, aging_hari: int|null, aging_label: string}  $row
     */
    public function map($row): array
    {
        $p = $row['payment'];

        return [
            'INV-'.str_pad((string) $p->id, 5, '0', STR_PAD_LEFT),
            optional($p->siswa)->nama,
            optional(optional($p->siswa)->cabang)->nama_cabang,
            optional($p->fee)->nama_biaya,
            (int) round((float) $p->nominal),
            optional($p->tanggal_bayar)?->format('Y-m-d'),
            optional($p->due_date)?->format('Y-m-d'),
            $row['aging_hari'] ?? '',
            $row['aging_label'],
        ];
    }

    public function title(): string
    {
        return 'Tagihan_outstanding';
    }
}
