<?php

namespace App\Exports;

use App\Models\Tutor;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TutorExport implements FromCollection, WithHeadings, WithMapping
{
    private $service;
    private $request;

    public function __construct(ManagementService $service, Request $request)
    {
        $this->service = $service;
        $this->request = $request;
    }

    public function collection()
    {
        // Use the same filtering logic as index, but without pagination
        $query = Tutor::query()->with('cabang:id,nama_cabang');

        if ($search = $this->request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        if ($this->request->filled('cabang_id')) {
            $query->where('cabang_id', $this->request->integer('cabang_id'));
        }

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->string('status')->toString());
        }

        // Apply admin_cabang scope if applicable (from service)
        $user = auth()->user();
        if ($user && $user->hasRole('admin_cabang')) {
            $cabangId = \App\Models\Cabang::query()->where('user_id', $user->id)->value('id');
            if ($cabangId) {
                $query->where('cabang_id', $cabangId);
            }
        }

        return $query->get();
    }

    public function map($tutor): array
    {
        return [
            $tutor->nama,
            $tutor->email,
            $tutor->nik,
            $tutor->no_hp,
            $tutor->cabang->nama_cabang ?? '-',
            ucfirst($tutor->jenis_tutor),
            ucfirst($tutor->status),
            $tutor->nama_bank,
            $tutor->atas_nama,
            $tutor->nomor_rekening,
            $tutor->alamat,
        ];
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Email',
            'NIK',
            'No. HP',
            'Cabang',
            'Jenis',
            'Status',
            'Nama Bank',
            'Atas Nama',
            'No. Rekening',
            'Alamat',
        ];
    }
}
