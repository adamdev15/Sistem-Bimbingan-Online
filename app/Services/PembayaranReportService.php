<?php

namespace App\Services;

use App\Models\Payment;
use App\Services\SuperAdmin\ManagementService;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PembayaranReportService
{
    public function __construct(private readonly ManagementService $management) {}

    /**
     * @return array<string, mixed>
     */
    public function ringkasanPayload(Request $request): array
    {
        $summary = $this->management->pembayaranSummary($request);
        $base = $this->management->pembayaranFilteredQuery($request);

        $perCabang = (clone $base)
            ->join('siswas', 'siswas.id', '=', 'payments.student_id')
            ->join('cabangs', 'cabangs.id', '=', 'siswas.cabang_id')
            ->select([
                'cabangs.nama_cabang',
                DB::raw('SUM(payments.nominal) as total_terbit'),
                DB::raw("SUM(CASE WHEN payments.status = 'lunas' THEN payments.nominal ELSE 0 END) as nominal_lunas"),
                DB::raw("SUM(CASE WHEN payments.status = 'belum' THEN payments.nominal ELSE 0 END) as nominal_belum"),
                DB::raw("SUM(CASE WHEN payments.status = 'lunas' THEN 1 ELSE 0 END) as trx_lunas"),
                DB::raw("SUM(CASE WHEN payments.status = 'belum' THEN 1 ELSE 0 END) as trx_belum"),
            ])
            ->groupBy('cabangs.id', 'cabangs.nama_cabang')
            ->orderByDesc('nominal_lunas')
            ->get();

        $periodCol = $this->periodColumnExpr();

        $perPeriode = (clone $base)
            ->select([
                DB::raw("{$periodCol} as periode_label"),
                DB::raw('SUM(payments.nominal) as total_terbit'),
                DB::raw("SUM(CASE WHEN payments.status = 'lunas' THEN payments.nominal ELSE 0 END) as nominal_lunas"),
                DB::raw("SUM(CASE WHEN payments.status = 'belum' THEN payments.nominal ELSE 0 END) as nominal_belum"),
            ])
            ->groupBy(DB::raw($periodCol))
            ->orderBy('periode_label')
            ->get();

        $topCabang = $perCabang->sortByDesc('nominal_lunas')->first();

        $siswaBelum = $this->siswaBelumBayarAggregate($request);

        $filterLabel = $this->filterDescription($request);

        return [
            'generated_at' => now(),
            'filter_label' => $filterLabel,
            'summary' => $summary,
            'per_cabang' => $perCabang,
            'per_periode' => $perPeriode,
            'top_cabang' => $topCabang,
            'siswa_belum_bayar' => $siswaBelum,
            'insight_cabang' => $topCabang
                ? 'Cabang dengan pemasukan lunas tertinggi (sesuai filter): '.$topCabang->nama_cabang.' (Rp '.number_format((int) $topCabang->nominal_lunas, 0, ',', '.').').'
                : 'Tidak ada data cabang pada filter ini.',
            'insight_siswa' => $siswaBelum->isNotEmpty()
                ? 'Siswa dengan total tagihan belum lunas terbesar: '.$siswaBelum->first()['nama'].' — '.$siswaBelum->first()['jumlah_tagihan'].' tagihan (Rp '.number_format((int) $siswaBelum->first()['total_nominal'], 0, ',', '.').').'
                : 'Tidak ada agregasi siswa belum lunas pada filter ini (atau semua sudah lunas).',
        ];
    }

    /**
     * @return Collection<int, array{nama: string, cabang: string, jumlah_tagihan: int, total_nominal: float|int}>
     */
    public function siswaBelumBayarAggregate(Request $request): Collection
    {
        $r = Request::create($request->url(), 'GET', array_merge($request->query(), ['status' => 'belum']));

        $rows = $this->management->pembayaranFilteredQuery($r)
            ->join('siswas', 'siswas.id', '=', 'payments.student_id')
            ->join('cabangs', 'cabangs.id', '=', 'siswas.cabang_id')
            ->select([
                'siswas.nama',
                'cabangs.nama_cabang',
                DB::raw('COUNT(payments.id) as jumlah_tagihan'),
                DB::raw('SUM(payments.nominal) as total_nominal'),
            ])
            ->groupBy('siswas.id', 'siswas.nama', 'cabangs.nama_cabang')
            ->orderByDesc(DB::raw('SUM(payments.nominal)'))
            ->limit(50)
            ->get();

        return $rows->map(fn ($row) => [
            'nama' => (string) $row->nama,
            'cabang' => (string) $row->nama_cabang,
            'jumlah_tagihan' => (int) $row->jumlah_tagihan,
            'total_nominal' => (float) $row->total_nominal,
        ]);
    }

    public function outstandingPaginator(Request $request): LengthAwarePaginator
    {
        $r = Request::create($request->url(), 'GET', array_merge($request->query(), ['status' => 'belum']));

        return $this->management->pembayaranFilteredQuery($r)
            ->with([
                'siswa:id,nama,cabang_id',
                'siswa.cabang:id,nama_cabang',
                'fee:id,nama_biaya',
            ])
            ->orderByRaw('CASE WHEN payments.due_date IS NULL THEN 1 ELSE 0 END, payments.due_date ASC')
            ->latest('payments.tanggal_bayar')
            ->paginate(12, ['*'], 'outstanding_page')
            ->withQueryString()
            ->through(function (Payment $p) {
                $meta = $this->agingMeta($p);

                return [
                    'payment' => $p,
                    'aging_hari' => $meta['hari_telat'],
                    'aging_label' => $meta['label'],
                ];
            });
    }

    /**
     * @return Collection<int, Payment>
     */
    public function outstandingForExport(Request $request): Collection
    {
        $r = Request::create($request->url(), 'GET', array_merge($request->query(), ['status' => 'belum']));

        return $this->management->pembayaranFilteredQuery($r)
            ->with([
                'siswa.cabang',
                'fee',
            ])
            ->orderByRaw('CASE WHEN payments.due_date IS NULL THEN 1 ELSE 0 END, payments.due_date ASC')
            ->latest('payments.tanggal_bayar')
            ->get();
    }

    /**
     * @return array{hari_telat: int|null, label: string}
     */
    public function agingMeta(Payment $payment): array
    {
        $due = $payment->due_date;
        if (! $due instanceof CarbonInterface) {
            return ['hari_telat' => null, 'label' => 'Tanpa jatuh tempo'];
        }

        $due = $due->copy()->startOfDay();
        $today = now()->startOfDay();

        if ($due->greaterThan($today)) {
            $hari = (int) $today->diffInDays($due);

            return ['hari_telat' => -$hari, 'label' => $hari.' hari lagi'];
        }

        if ($due->equalTo($today)) {
            return ['hari_telat' => 0, 'label' => 'Jatuh tempo hari ini'];
        }

        $telat = (int) $due->diffInDays($today);

        return ['hari_telat' => $telat, 'label' => $telat.' hari telat'];
    }

    public function outstandingPayloadForPdf(Request $request): array
    {
        $rows = $this->outstandingForExport($request);
        $mapped = $rows->map(function (Payment $p) {
            $meta = $this->agingMeta($p);

            return [
                'payment' => $p,
                'aging_hari' => $meta['hari_telat'],
                'aging_label' => $meta['label'],
            ];
        });

        $totalNominal = (int) $rows->sum(fn (Payment $p) => (int) round((float) $p->nominal));
        $telatCount = $mapped->filter(fn (array $x) => ($x['aging_hari'] ?? 0) > 0)->count();

        return [
            'generated_at' => now(),
            'filter_label' => $this->filterDescription($request).' · hanya belum lunas',
            'rows' => $mapped,
            'total_tagihan' => $rows->count(),
            'total_nominal' => $totalNominal,
            'jumlah_telat' => $telatCount,
            'insight_cashflow' => $totalNominal > 0
                ? 'Outstanding Rp '.number_format($totalNominal, 0, ',', '.').' mempengaruhi proyeksi kas masuk. Prioritaskan penagihan pada tagihan yang sudah lewat jatuh tempo.'
                : 'Tidak ada outstanding pada filter ini.',
            'insight_wa' => 'Gunakan tombol "Pengingat jatuh tempo" di halaman pembayaran untuk broadcast notifikasi in-app; integrasi WhatsApp dapat dihubungkan pada notifier yang sama.',
        ];
    }

    private function filterDescription(Request $request): string
    {
        $parts = [];
        if ($request->filled('bulan')) {
            $parts[] = 'Periode terbit '.$request->string('bulan');
        }
        if ($request->string('status')->toString()) {
            $parts[] = 'Status '.$request->string('status');
        }
        if ($request->filled('student_id')) {
            $parts[] = 'Siswa ID '.$request->integer('student_id');
        }
        if ($parts === []) {
            return 'Semua data (sesuai hak akses cabang)';
        }

        return implode(' · ', $parts);
    }

    private function periodColumnExpr(): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => "strftime('%Y-%m', payments.tanggal_bayar)",
            default => "DATE_FORMAT(payments.tanggal_bayar, '%Y-%m')",
        };
    }
}
