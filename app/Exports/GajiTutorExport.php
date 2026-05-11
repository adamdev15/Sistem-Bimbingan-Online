<?php

namespace App\Exports;

use App\Models\Salary;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GajiTutorExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->with(['tutor', 'creator'])->get();
    }

    public function headings(): array
    {
        return [
            'Nama Tutor',
            'Periode',
            'Start Date',
            'End Date',
            'Status',
            'Gaji Pokok',
            'Insentif Kehadiran',
            'Bonus Lainnya',
            'Total Gaji',
            'Dicatat Oleh',
            'Tanggal Input',
            'Total Kehadiran',
            'Catatan'
        ];
    }

    public function map($salary): array
    {
        return [
            $salary->tutor?->nama,
            $salary->periode,
            $salary->start_date,
            $salary->end_date,
            $salary->status,
            $salary->gaji,
            $salary->insentif_kehadiran,
            $salary->bonus_lainnya,
            $salary->total_gaji,
            $salary->creator?->name,
            $salary->created_at->format('Y-m-d H:i:s'),
            $salary->total_kehadiran,
            $salary->catatan
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '22C55E']
                ]
            ],
        ];
    }
}
