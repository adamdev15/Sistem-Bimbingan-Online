<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\KategoriPengeluaran;
use App\Models\Payment;
use App\Models\Pengeluaran;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanKeuanganController extends Controller
{
    private function getCabangId(): ?int
    {
        $user = Auth::user();
        if ($user->hasRole('admin_cabang')) {
            return Cabang::where('user_id', $user->id)->value('id');
        }
        return null;
    }

    public function index(Request $request): View
    {
        $cabangId = $this->getCabangId();
        $cabangs = Cabang::all();
        $selectedCabangId = $request->cabang_id ?? $cabangId;
        $filterCabangId = $selectedCabangId === 'all' ? null : $selectedCabangId;

        $month = $request->string('month', now()->format('Y-m'))->toString();
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        // Simple KPI cards
        $income = Payment::where('status', 'lunas')
            ->whereBetween('tanggal_bayar', [$start, $end])
            ->when($filterCabangId, fn($q) => $q->whereHas('siswa', fn($s) => $s->withTrashed()->where('cabang_id', $filterCabangId)))
            ->sum('nominal');

        $expense = Pengeluaran::whereBetween('tanggal', [$start, $end])
            ->when($filterCabangId, fn($q) => $q->where('cabang_id', $filterCabangId))
            ->sum('nominal');

        $date = Carbon::parse($month);
        $monthNameId = $date->translatedFormat('F');
        $monthNameEn = $date->format('F');
        $year = $date->year;

        $salaries = Salary::whereIn('status', ['dibayar', 'diterima'])
            ->where(function($q) use ($month, $monthNameId, $monthNameEn, $year) {
                $q->where('periode', $month)
                  ->orWhere('periode', 'like', "%{$monthNameId}%{$year}%")
                  ->orWhere('periode', 'like', "%{$monthNameEn}%{$year}%");
            })
            ->when($filterCabangId, fn($q) => $q->whereHas('tutor', fn($t) => $t->where('cabang_id', $filterCabangId)))
            ->sum('total_gaji');

        // Logic for Chart Overview (Annual Data - 12 Months)
        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $incomeSeries = [];
        $expenseSeries = [];
        $profitSeries = [];

        for ($m = 1; $m <= 12; $m++) {
            $mDate = Carbon::create($year, $m, 1);
            $mStart = $mDate->copy()->startOfMonth();
            $mEnd = $mDate->copy()->endOfMonth();
            
            // 1. Monthly Income
            $inc = Payment::where('status', 'lunas')
                ->whereBetween('tanggal_bayar', [$mStart, $mEnd])
                ->when($filterCabangId, fn($q) => $q->whereHas('siswa', fn($s) => $s->withTrashed()->where('cabang_id', $filterCabangId)))
                ->sum('nominal');

            // 2. Monthly Operational Expenses
            $expOp = Pengeluaran::whereBetween('tanggal', [$mStart, $mEnd])
                ->when($filterCabangId, fn($q) => $q->where('cabang_id', $filterCabangId))
                ->sum('nominal');

            // 3. Monthly Tutor Salaries
            $mNameId = $mDate->translatedFormat('F');
            $mNameEn = $mDate->format('F');
            $pSearch = "%{$mNameId}%{$year}%";
            $pSearchEn = "%{$mNameEn}%{$year}%";
            
            $expSal = Salary::whereIn('status', ['dibayar', 'diterima'])
                ->where(function($q) use ($mDate, $pSearch, $pSearchEn) {
                    $q->where('periode', $mDate->format('Y-m'))
                      ->orWhere('periode', 'like', $pSearch)
                      ->orWhere('periode', 'like', $pSearchEn);
                })
                ->when($filterCabangId, fn($q) => $q->whereHas('tutor', fn($t) => $t->where('cabang_id', $filterCabangId)))
                ->sum('total_gaji');

            $totalExp = (float)$expOp + (float)$expSal;
            
            $incomeSeries[] = (float)$inc;
            $expenseSeries[] = $totalExp;
            $profitSeries[] = (float)$inc - $totalExp;
        }

        return view('modules.laporan-keuangan.index', [
            'cabangId' => $cabangId,
            'cabangs' => $cabangs,
            'selectedCabangId' => $selectedCabangId,
            'month' => $month,
            'income' => $income,
            'expense' => $expense,
            'salaries' => $salaries,
            'net' => $income - $expense - $salaries,
            'chartData' => [
                'labels' => $chartLabels,
                'income' => $incomeSeries,
                'expense' => $expenseSeries,
                'profit' => $profitSeries
            ]
        ]);
    }

    public function harian(Request $request): View
    {
        $cabangId = $this->getCabangId();
        $selectedCabangId = $request->cabang_id ?? $cabangId;
        $filterCabangId = $selectedCabangId === 'all' ? null : $selectedCabangId;

        $month = $request->string('month', now()->format('Y-m'))->toString();
        
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        // 1. Get Daily Income
        $dailyIncome = Payment::where('status', 'lunas')
            ->whereBetween('tanggal_bayar', [$start, $end])
            ->when($filterCabangId, fn($q) => $q->whereHas('siswa', fn($s) => $s->withTrashed()->where('cabang_id', $filterCabangId)))
            ->selectRaw('DATE(tanggal_bayar) as date, SUM(nominal) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // 2. Get Daily Expenses
        $dailyExpense = Pengeluaran::whereBetween('tanggal', [$start, $end])
            ->when($filterCabangId, fn($q) => $q->where('cabang_id', $filterCabangId))
            ->selectRaw('DATE(tanggal) as date, SUM(nominal) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // 3. Merge and Build Ledger
        $ledger = [];
        $runningBalance = 0;
        
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $currentDate = $date->format('Y-m-d');
            $income = $dailyIncome->get($currentDate, 0);
            $expense = $dailyExpense->get($currentDate, 0);
            $net = $income - $expense;
            $runningBalance += $net;

            $ledger[] = [
                'tanggal' => $date->copy(),
                'pemasukan' => $income,
                'pengeluaran' => $expense,
                'jumlah' => $income - $expense,
                'saldo' => $runningBalance
            ];
        }

        return view('modules.laporan-keuangan.harian', [
            'cabangId' => $cabangId,
            'ledger' => $ledger,
            'month' => $month,
            'selectedCabangId' => $selectedCabangId,
            'cabang' => $filterCabangId ? Cabang::find($filterCabangId) : null
        ]);
    }

    public function rekapBulanan(Request $request): View
    {
        $cabangId = $this->getCabangId();
        $selectedCabangId = $request->cabang_id ?? $cabangId;
        
        if (!$selectedCabangId && !Auth::user()->hasRole('super_admin')) {
            return redirect()->route('laporan-keuangan.index')->withErrors(['cabang' => 'Pilih cabang untuk melihat laporan bulanan.']);
        }

        $filterCabangId = $selectedCabangId === 'all' ? null : $selectedCabangId;

        $month = $request->string('month', now()->format('Y-m'))->toString();
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();
        $cabang = Cabang::find($selectedCabangId);

        $payments = Payment::with(['siswa.materiLes', 'fee'])
            ->where('status', 'lunas')
            ->whereBetween('tanggal_bayar', [$start, $end])
            ->whereHas('siswa', fn($s) => $s->withTrashed()->where('cabang_id', $selectedCabangId))
            ->get();

        $aggregated = [];
        $rekapIncome = [];
        $totalIncome = 0;

        foreach ($payments as $pay) {
            $label = $this->getPaymentLabel($pay);
            $aggregated[$label] = ($aggregated[$label] ?? 0) + (float) $pay->nominal;
            $totalIncome += (float) $pay->nominal;
        }

        foreach ($aggregated as $label => $nominal) {
            $rekapIncome[] = ['keterangan' => $label, 'nominal' => $nominal];
        }

        // EXPENSES (Operasional)
        $totalOperasional = Pengeluaran::whereBetween('tanggal', [$start, $end])
            ->where('cabang_id', $selectedCabangId)
            ->sum('nominal');

        $date = Carbon::parse($month);
        $monthNameId = $date->translatedFormat('F');
        $monthNameEn = $date->format('F');
        $year = $date->year;

        $totalSalaries = Salary::whereIn('status', ['dibayar', 'diterima'])
            ->where(function($q) use ($month, $monthNameId, $monthNameEn, $year) {
                $q->where('periode', $month)
                  ->orWhere('periode', 'like', "%{$monthNameId}%{$year}%")
                  ->orWhere('periode', 'like', "%{$monthNameEn}%{$year}%");
            })
            ->whereHas('tutor', fn($t) => $t->where('cabang_id', $selectedCabangId))
            ->sum('total_gaji');

        // Total Expenses might include salaries too, but based on image, "Operasional" is the main one shown.
        // We'll follow the image structure.
        
        return view('modules.laporan-keuangan.rekap', [
            'month' => $month,
            'cabang' => $cabang,
            'rekapIncome' => $rekapIncome,
            'totalOperasional' => $totalOperasional,
            'totalSalaries' => $totalSalaries,
            'totalIncome' => $totalIncome,
            'selectedCabangId' => $selectedCabangId
        ]);
    }

    public function bulanan(Request $request): View
    {
        $cabangId = $this->getCabangId();
        $selectedCabangId = $request->cabang_id ?? $cabangId;

        if (!$selectedCabangId && !Auth::user()->hasRole('super_admin')) {
            return redirect()->route('laporan-keuangan.index')->withErrors(['cabang' => 'Pilih cabang untuk melihat laporan bulanan.']);
        }

        $filterCabangId = $selectedCabangId === 'all' ? null : $selectedCabangId;

        $month = $request->string('month', now()->format('Y-m'))->toString();
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();
        $cabang = Cabang::find($selectedCabangId);

        // INCOME breakdown - dynamic by material for SPP
        $payments = Payment::with(['siswa.materiLes', 'fee'])
            ->where('status', 'lunas')
            ->whereBetween('tanggal_bayar', [$start, $end])
            ->whereHas('siswa', fn($s) => $s->withTrashed()->where('cabang_id', $selectedCabangId))
            ->get();

        $incomeBreakdown = [];
        foreach ($payments as $pay) {
            $label = $this->getPaymentLabel($pay);
            $incomeBreakdown[$label] = ($incomeBreakdown[$label] ?? 0) + (float) $pay->nominal;
        }
        $incomeBreakdown = collect($incomeBreakdown)->map(fn($total, $label) => (object)['nama_biaya' => $label, 'total' => $total])->values();

        // EXPENSE breakdown by Category
        $expenseBreakdown = Pengeluaran::whereBetween('tanggal', [$start, $end])
            ->where('cabang_id', $selectedCabangId)
            ->join('kategori_pengeluarans', 'pengeluarans.kategori_id', '=', 'kategori_pengeluarans.id')
            ->select('kategori_pengeluarans.nama_kategori', DB::raw('SUM(pengeluarans.nominal) as total'))
            ->groupBy('kategori_pengeluarans.nama_kategori')
            ->get();

        // SALARIES (Honor Guru)
        $date = Carbon::parse($month);
        $monthNameId = $date->translatedFormat('F');
        $monthNameEn = $date->format('F');
        $year = $date->year;

        $totalSalaries = Salary::whereIn('status', ['dibayar', 'diterima'])
            ->where(function($q) use ($month, $monthNameId, $monthNameEn, $year) {
                $q->where('periode', $month)
                  ->orWhere('periode', 'like', "%{$monthNameId}%{$year}%")
                  ->orWhere('periode', 'like', "%{$monthNameEn}%{$year}%");
            })
            ->whereHas('tutor', fn($t) => $t->where('cabang_id', $selectedCabangId))
            ->sum('total_gaji');

        $totalIncome = $incomeBreakdown->sum('total');
        $totalExpenses = $expenseBreakdown->sum('total') + $totalSalaries;
        $netProfit = $totalIncome - $totalExpenses;

        // Bagi Hasil Calculation
        $shares = [
            'investor_pct' => $cabang ? $cabang->profit_share_investor : 0,
            'pusat_pct' => $cabang ? $cabang->profit_share_pusat : 0,
            'investor_amount' => ($cabang && $netProfit > 0) ? ($netProfit * $cabang->profit_share_investor / 100) : 0,
            'pusat_amount' => ($cabang && $netProfit > 0) ? ($netProfit * $cabang->profit_share_pusat / 100) : 0,
        ];

        return view('modules.laporan-keuangan.bulanan', [
            'cabangId' => $cabangId,
            'month' => $month,
            'cabang' => $cabang,
            'incomeBreakdown' => $incomeBreakdown,
            'expenseBreakdown' => $expenseBreakdown,
            'totalSalaries' => $totalSalaries,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'shares' => $shares
        ]);
    }

    public function exportPdf(Request $request)
    {
        $type = $request->query('type', 'harian');
        $cabangId = $this->getCabangId();
        $selectedCabangId = $request->cabang_id ?? $cabangId;
        $filterCabangId = $selectedCabangId === 'all' ? null : $selectedCabangId;

        $month = $request->string('month', now()->format('Y-m'))->toString();
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();
        $cabang = $filterCabangId ? Cabang::find($filterCabangId) : null;

        $view = 'modules.laporan-keuangan.pdf.' . $type;
        $data = [
            'month' => $month,
            'cabang' => $cabang,
            'selectedCabangId' => $selectedCabangId,
        ];

        if ($type === 'harian') {
            $dailyIncome = Payment::where('status', 'lunas')
                ->whereBetween('tanggal_bayar', [$start, $end])
                ->when($filterCabangId, fn($q) => $q->whereHas('siswa', fn($s) => $s->withTrashed()->where('cabang_id', $filterCabangId)))
                ->selectRaw('DATE(tanggal_bayar) as date, SUM(nominal) as total')
                ->groupBy('date')
                ->pluck('total', 'date');
            $dailyExpense = Pengeluaran::whereBetween('tanggal', [$start, $end])
                ->when($filterCabangId, fn($q) => $q->where('cabang_id', $filterCabangId))
                ->selectRaw('DATE(tanggal) as date, SUM(nominal) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            $ledger = [];
            $runningBalance = 0;
            for ($date = $start->copy(); $date <= $end; $date->addDay()) {
                $currentDate = $date->format('Y-m-d');
                $income = $dailyIncome->get($currentDate, 0);
                $expense = $dailyExpense->get($currentDate, 0);
                $net = $income - $expense;
                $runningBalance += $net;
                $ledger[] = ['tanggal' => $date->copy(), 'pemasukan' => $income, 'pengeluaran' => $expense, 'jumlah' => $net, 'saldo' => $runningBalance];
            }
            $data['ledger'] = $ledger;
            $filename = 'Laporan_Harian_' . $month . '.pdf';
        } elseif ($type === 'bulanan') {
            $payments = Payment::with(['siswa.materiLes', 'fee'])
                ->where('status', 'lunas')
                ->whereBetween('tanggal_bayar', [$start, $end])
                ->when($filterCabangId, fn($q) => $q->whereHas('siswa', fn($s) => $s->withTrashed()->where('cabang_id', $filterCabangId)))
                ->get();

            $aggregated = [];
            $rekapIncome = [];
            $totalIncome = 0;

            foreach ($payments as $pay) {
                $label = $this->getPaymentLabel($pay);
                $aggregated[$label] = ($aggregated[$label] ?? 0) + (float) $pay->nominal;
                $totalIncome += (float) $pay->nominal;
            }

            foreach ($aggregated as $label => $nominal) {
                $rekapIncome[] = ['keterangan' => $label, 'nominal' => $nominal];
            }

            $totalOperasional = Pengeluaran::whereBetween('tanggal', [$start, $end])->when($filterCabangId, fn($q) => $q->where('cabang_id', $filterCabangId))->sum('nominal');
            
            $date = Carbon::parse($month);
            $monthNameId = $date->translatedFormat('F');
            $monthNameEn = $date->format('F');
            $year = $date->year;

            $totalSalaries = Salary::whereIn('status', ['dibayar', 'diterima'])
                ->where(function($q) use ($month, $monthNameId, $monthNameEn, $year) {
                    $q->where('periode', $month)
                      ->orWhere('periode', 'like', "%{$monthNameId}%{$year}%")
                      ->orWhere('periode', 'like', "%{$monthNameEn}%{$year}%");
                })
                ->when($filterCabangId, fn($q) => $q->whereHas('tutor', fn($t) => $t->where('cabang_id', $filterCabangId)))
                ->sum('total_gaji');

            $data['rekapIncome'] = $rekapIncome;
            $data['totalIncome'] = $totalIncome;
            $data['totalOperasional'] = $totalOperasional;
            $data['totalSalaries'] = $totalSalaries;
            $filename = 'Laporan_Bulanan_' . $month . '.pdf';
        } elseif ($type === 'mitra') {
            $payments = Payment::with(['siswa.materiLes', 'fee'])
                ->where('status', 'lunas')
                ->whereBetween('tanggal_bayar', [$start, $end])
                ->when($filterCabangId, fn($q) => $q->whereHas('siswa', fn($s) => $s->withTrashed()->where('cabang_id', $filterCabangId)))
                ->get();

            $incomeBreakdown = [];
            foreach ($payments as $pay) {
                $label = $this->getPaymentLabel($pay);
                $incomeBreakdown[$label] = ($incomeBreakdown[$label] ?? 0) + (float) $pay->nominal;
            }

            $incomeBreakdown = collect($incomeBreakdown)->map(fn($total, $label) => (object)['nama_biaya' => $label, 'total' => $total])->values();

            $expenseBreakdown = Pengeluaran::whereBetween('tanggal', [$start, $end])->when($filterCabangId, fn($q) => $q->where('cabang_id', $filterCabangId))
                ->join('kategori_pengeluarans', 'pengeluarans.kategori_id', '=', 'kategori_pengeluarans.id')
                ->select('kategori_pengeluarans.nama_kategori', DB::raw('SUM(pengeluarans.nominal) as total'))->groupBy('kategori_pengeluarans.nama_kategori')->get();
            
            $date = Carbon::parse($month);
            $monthNameId = $date->translatedFormat('F');
            $monthNameEn = $date->format('F');
            $year = $date->year;

            $totalSalaries = Salary::whereIn('status', ['dibayar', 'diterima'])
                ->where(function($q) use ($month, $monthNameId, $monthNameEn, $year) {
                    $q->where('periode', $month)
                      ->orWhere('periode', 'like', "%{$monthNameId}%{$year}%")
                      ->orWhere('periode', 'like', "%{$monthNameEn}%{$year}%");
                })
                ->when($filterCabangId, fn($q) => $q->whereHas('tutor', fn($t) => $t->where('cabang_id', $filterCabangId)))
                ->sum('total_gaji');
            $totalIncome = $incomeBreakdown->sum('total');
            $totalExpenses = $expenseBreakdown->sum('total') + $totalSalaries;
            $netProfit = $totalIncome - $totalExpenses;
            $data['incomeBreakdown'] = $incomeBreakdown;
            $data['expenseBreakdown'] = $expenseBreakdown;
            $data['totalSalaries'] = $totalSalaries;
            $data['totalIncome'] = $totalIncome;
            $data['totalExpenses'] = $totalExpenses;
            $data['netProfit'] = $netProfit;
            $data['shares'] = [
                'investor_pct' => $cabang ? $cabang->profit_share_investor : 0, 
                'pusat_pct' => $cabang ? $cabang->profit_share_pusat : 0,
                'investor_amount' => ($cabang && $netProfit > 0) ? ($netProfit * $cabang->profit_share_investor / 100) : 0,
                'pusat_amount' => ($cabang && $netProfit > 0) ? ($netProfit * $cabang->profit_share_pusat / 100) : 0,
            ];
            $filename = 'Laporan_Mitra_' . $month . '.pdf';
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($view, $data);
        return $pdf->stream($filename);
    }

    private function getPaymentLabel(Payment $pay): string
    {
        $feeName = $pay->fee?->nama_biaya ?? 'Lain-lain';
        $feeLower = strtolower($feeName);

        $isRegistration = str_contains($feeLower, 'pendaftaran');
        $isMonthly = str_contains($feeLower, 'spp') ||
                     str_contains($feeLower, 'matematika intensif') ||
                     str_contains($feeLower, 'kelas prima') ||
                     str_contains($feeLower, 'jarimatika') ||
                     str_contains($feeLower, 'calistung') ||
                     str_contains($feeLower, 'iec');

        if ($isRegistration) {
            return $feeName;
        } elseif ($isMonthly) {
            $materi = $pay->siswa?->materiLes?->nama_materi ?? 'Lain-lain';
            return "SPP - " . $materi;
        }

        return $feeName;
    }
}
