<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PembayaranExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->with(['siswa', 'fee'])->get();
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Nama Siswa',
            'Jenis Biaya',
            'Periode Tagihan',
            'Nominal',
            'Tanggal Bayar',
            'Due Date',
            'Status',
            'Dicatat Oleh',
            'Tanggal Jatuh Tempo',
            'Catatan'
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->order_id,
            $payment->siswa?->nama,
            $payment->fee?->nama_biaya,
            $payment->invoice_period,
            $payment->nominal,
            $payment->tanggal_bayar,
            $payment->due_date,
            $payment->status,
            $payment->creator?->name,
            $payment->tanggal_jatuh_tempo,
            $payment->catatan
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
