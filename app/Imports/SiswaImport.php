<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\Cabang;
use App\Models\MateriLes;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;

class SiswaImport implements ToModel, WithHeadingRow, WithValidation
{
    private $cabangs;
    private $materis;

    public function __construct()
    {
        $this->cabangs = Cabang::pluck('id', 'nama_cabang')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        });
        $this->materis = MateriLes::pluck('id', 'nama_materi')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        });
    }

    public function model(array $row)
    {
        $cabangInput = trim($row['cabang'] ?? '');
        $materiInput = trim($row['materi_les'] ?? '');

        // Support both ID and Name
        $cabangId = is_numeric($cabangInput) ? (int)$cabangInput : ($this->cabangs[strtolower($cabangInput)] ?? null);
        $materiId = is_numeric($materiInput) ? (int)$materiInput : ($this->materis[strtolower($materiInput)] ?? null);

        // If admin_cabang, force their cabang
        $user = Auth::user();
        if ($user->hasRole('admin_cabang')) {
            $cabangId = Cabang::where('user_id', $user->id)->value('id');
        }

        return new Siswa([
            'nama'              => $row['nama'],
            'jenis_kelamin'     => strtolower($row['jenis_kelamin'] ?? '') === 'perempuan' ? 'perempuan' : 'laki_laki',
            'nik'               => $row['nik'] ?? null,
            'no_hp'             => $row['no_hp'] ?? '-',
            'alamat'            => $row['alamat'] ?? '-',
            'cabang_id'         => $cabangId,
            'status'            => 'aktif',
            'tempat_lahir'      => $row['tempat_lahir'] ?? null,
            'tanggal_lahir'     => $this->transformDate($row['tanggal_lahir'] ?? null),
            'asal_sekolah'      => $row['asal_sekolah'] ?? null,
            'nis'               => $row['nis'] ?? null,
            'materi_les_id'     => $materiId,
            'nama_ayah'         => $row['nama_ayah'] ?? null,
            'nama_ibu'          => $row['nama_ibu'] ?? null,
            'no_hp_orang_tua'   => $row['no_hp_orang_tua'] ?? null,
            'registration_type' => strtolower($row['jenis_siswa'] ?? '') === 'lama' ? 'lama' : 'baru',
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => 'required',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan,laki_laki,perempuan',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Kolom nama wajib diisi.',
            'jenis_kelamin.required' => 'Kolom jenis kelamin wajib diisi.',
            'jenis_kelamin.in' => 'Format jenis kelamin salah. Gunakan: Laki-laki atau Perempuan.',
        ];
    }

    private function transformDate($value)
    {
        if (!$value) return null;
        
        // If it's already a numeric (Excel date serial)
        if (is_numeric($value)) {
            try {
                return \Carbon\Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            } catch (\Exception $e) {
                // Fallback to parse if serial conversion fails
            }
        }

        // If it's a string date (e.g. "2024-05-11")
        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
