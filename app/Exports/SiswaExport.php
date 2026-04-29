<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->get();
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'Jenis Kelamin',
            'NIK',
            'No HP',
            'Alamat',
            'Cabang',
            'Status',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Asal Sekolah',
            'NIS',
            'Materi Les',
            'Nama Ayah',
            'Tempat Lahir Ayah',
            'Tanggal Lahir Ayah',
            'Pekerjaan Ayah',
            'Nama Ibu',
            'Tempat Lahir Ibu',
            'Tanggal Lahir Ibu',
            'Pekerjaan Ibu',
            'No HP Orang Tua'
        ];
    }

    public function map($siswa): array
    {
        return [
            $siswa->nama,
            $siswa->email,
            $siswa->jenis_kelamin,
            $siswa->nik,
            $siswa->no_hp,
            $siswa->alamat,
            $siswa->cabang?->nama_cabang,
            $siswa->status,
            $siswa->tempat_lahir,
            $siswa->tanggal_lahir,
            $siswa->asal_sekolah,
            $siswa->nis,
            $siswa->materiLes?->nama_materi,
            $siswa->nama_ayah,
            $siswa->tempat_lahir_ayah,
            $siswa->tanggal_lahir_ayah,
            $siswa->pekerjaan_ayah,
            $siswa->nama_ibu,
            $siswa->tempat_lahir_ibu,
            $siswa->tanggal_lahir_ibu,
            $siswa->pekerjaan_ibu,
            $siswa->no_hp_orang_tua
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
