<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PresensiController extends Controller
{
    public function __construct(private readonly ManagementService $service) {}

    public function index(Request $request): View
    {
        return view('modules.presensi.index', [
            'presensis' => $this->service->presensiIndex($request),
            'summary' => $this->service->presensiSummary($request),
            'presensi_jadwals' => auth()->user()?->hasRole('siswa')
                ? $this->service->presensiJadwalFilterOptionsForSiswa()
                : collect(),
            'filters' => $request->only(['tanggal', 'status', 'jadwal_id']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->service->presensiIndex($request)->getCollection();

        return response()->streamDownload(function () use ($rows): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Nama', 'Sesi', 'Tutor', 'Status']);
            foreach ($rows as $row) {
                fputcsv($handle, [
                    optional($row->tanggal)->format('Y-m-d'),
                    optional($row->siswa)->nama,
                    optional($row->jadwal)->mapel,
                    optional($row->tutor)->nama ?? optional(optional($row->jadwal)->tutor)->nama ?? '',
                    $row->status,
                ]);
            }
            fclose($handle);
        }, 'rekap-presensi.csv', ['Content-Type' => 'text/csv']);
    }
}
