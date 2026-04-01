<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function __construct(private readonly ManagementService $service)
    {
    }

    public function index(Request $request): View
    {
        return view('modules.laporan.index', [
            ...$this->service->laporanData($request),
        ]);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $data = $this->service->laporanData($request);

        return response()->streamDownload(function () use ($data): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Cabang', 'Kategori', 'Jumlah']);
            foreach ($data['trx'] as $trx) {
                fputcsv($handle, [
                    optional($trx->tanggal_bayar)->format('Y-m-d'),
                    optional(optional($trx->siswa)->cabang)->nama_cabang,
                    optional($trx->fee)->nama_biaya,
                    $trx->nominal,
                ]);
            }
            fclose($handle);
        }, 'laporan.xlsx', ['Content-Type' => 'application/vnd.ms-excel']);
    }

    public function exportPdf(Request $request): StreamedResponse
    {
        $data = $this->service->laporanData($request);

        return response()->streamDownload(function () use ($data): void {
            echo "LAPORAN BMS\n";
            echo "Periode: {$data['start']->format('Y-m-d')} s/d {$data['end']->format('Y-m-d')}\n\n";
            foreach ($data['paymentByFee'] as $row) {
                echo "{$row->nama_biaya}: {$row->total_nominal}\n";
            }
        }, 'laporan.pdf', ['Content-Type' => 'application/pdf']);
    }
}
