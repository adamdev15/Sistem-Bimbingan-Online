<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SiswaTemplateExport implements WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return [
            'Nama',
            'Jenis_Kelamin',
            'NIK',
            'No_HP',
            'Alamat',
            'Cabang',
            'Tempat_Lahir',
            'Tanggal_Lahir',
            'Asal_Sekolah',
            'NIS',
            'Materi_Les',
            'Nama_Ayah',
            'Nama_Ibu',
            'No_HP_Orang_Tua',
            'Jenis_Siswa'
        ];
    }
}
