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
        return $this->query->with(['tutor', 'creator', 'items'])->get();
    }

    public function headings(): array
    {
        return [
            'Nama Tutor',
            'Periode',
            'Start Date',
            'End Date',
            'Status',
            'Full Day',
            'Pagi-Siang',
            'Siang-Sore',
            'Kelas Malam',
            'Bonus',
            'Lain-lainnya',
            'Total Gaji',
            'Dicatat Oleh',
            'Tanggal Input',
            'Catatan'
        ];
    }

    public function map($salary): array
    {
        return [
            $salary->tutor?->nama,
            $salary->periode,
            $salary->start_date?->format('Y-m-d'),
            $salary->end_date?->format('Y-m-d'),
            $salary->status,
            $salary->items->where('nama_item', 'Full')->sum('subtotal'),
            $salary->items->where('nama_item', 'Pagi-Siang')->sum('subtotal'),
            $salary->items->where('nama_item', 'Siang-Sore')->sum('subtotal'),
            $salary->items->where('nama_item', 'Kelas Malam')->sum('subtotal'),
            $salary->bonus,
            $salary->lain_lainnya,
            $salary->total_gaji,
            $salary->creator?->name,
            $salary->created_at->format('Y-m-d H:i:s'),
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
