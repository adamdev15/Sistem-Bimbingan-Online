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
        $selectedCabangId = $cabangId ?: $request->cabang_id;

        $month = $request->string('month', now()->format('Y-m'))->toString();
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        // Simple KPI cards
        $income = Payment::where('status', 'lunas')
            ->whereBetween('tanggal_bayar', [$start, $end])
            ->when($selectedCabangId, fn($q) => $q->whereHas('siswa', fn($s) => $s->where('cabang_id', $selectedCabangId)))
            ->sum('nominal');

        $expense = Pengeluaran::whereBetween('tanggal', [$start, $end])
            ->when($selectedCabangId, fn($q) => $q->where('cabang_id', $selectedCabangId))
            ->sum('nominal');

        $salaries = Salary::where('periode', $month)
            ->when($selectedCabangId, fn($q) => $q->whereHas('tutor', fn($t) => $t->where('cabang_id', $selectedCabangId)))
            ->sum('total_gaji');

        // Logic for Chart Overview (Daily Data)
        $dailyIncome = Payment::where('status', 'lunas')
            ->whereBetween('tanggal_bayar', [$start, $end])
            ->when($selectedCabangId, fn($q) => $q->whereHas('siswa', fn($s) => $s->where('cabang_id', $selectedCabangId)))
            ->selectRaw('DATE(tanggal_bayar) as date, SUM(nominal) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $dailyExpense = Pengeluaran::whereBetween('tanggal', [$start, $end])
            ->when($selectedCabangId, fn($q) => $q->where('cabang_id', $selectedCabangId))
            ->selectRaw('DATE(tanggal) as date, SUM(nominal) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // Note: Salaries are usually per month, but if we want per day we'd need payment dates. 
        // For the overview chart, we'll focus on Payments vs Operational Expenses as "Daily Cashflow".

        $chartLabels = [];
        $incomeSeries = [];
        $expenseSeries = [];
        $profitSeries = [];

        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $currentDate = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d M');
            
            $inc = $dailyIncome->get($currentDate, 0);
            $exp = $dailyExpense->get($currentDate, 0);
            
            $incomeSeries[] = $inc;
            $expenseSeries[] = $exp;
            $profitSeries[] = $inc - $exp;
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
        $selectedCabangId = $cabangId ?: $request->cabang_id;
        $month = $request->string('month', now()->format('Y-m'))->toString();
        
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();

        // 1. Get Daily Income
        $dailyIncome = Payment::where('status', 'lunas')
            ->whereBetween('tanggal_bayar', [$start, $end])
            ->when($selectedCabangId, fn($q) => $q->whereHas('siswa', fn($s) => $s->where('cabang_id', $selectedCabangId)))
            ->selectRaw('DATE(tanggal_bayar) as date, SUM(nominal) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // 2. Get Daily Expenses
        $dailyExpense = Pengeluaran::whereBetween('tanggal', [$start, $end])
            ->when($selectedCabangId, fn($q) => $q->where('cabang_id', $selectedCabangId))
            ->selectRaw('DATE(tanggal) as date, SUM(nominal) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        // 3. Merge and Build Ledger
        $ledger = [];
        $runningBalance = 0;
        
        // We might want to get the balance BEFORE this month started (Carry over)
        // For simplicity in this bimbel app, we'll start from 0 for the month display
        
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
                'jumlah' => $net,
                'saldo' => $runningBalance
            ];
        }

        return view('modules.laporan-keuangan.harian', [
            'cabangId' => $cabangId,
            'ledger' => $ledger,
            'month' => $month,
            'selectedCabangId' => $selectedCabangId,
            'cabang' => $selectedCabangId ? Cabang::find($selectedCabangId) : null
        ]);
    }

    public function rekapBulanan(Request $request): View
    {
        $cabangId = $this->getCabangId();
        $selectedCabangId = $cabangId ?: $request->cabang_id;
        if (!$selectedCabangId) {
            return redirect()->route('laporan-keuangan.index')->withErrors(['cabang' => 'Pilih cabang untuk melihat laporan bulanan.']);
        }

        $month = $request->string('month', now()->format('Y-m'))->toString();
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();
        $cabang = Cabang::find($selectedCabangId);

        // Specific categories for income
        $categories = [
            'Pendaftaran' => ['pendaftaran'],
            'SPP Baca' => ['spp baca', 'baca'],
            'SPP Mapel' => ['spp mapel', 'mapel'],
            'SPP Jarmat' => ['jarmat', 'jarimatrik'],
        ];

        $rekapIncome = [];
        $totalIncome = 0;

        foreach ($categories as $label => $terms) {
            $sum = Payment::where('payments.status', 'lunas')
                ->whereBetween('payments.tanggal_bayar', [$start, $end])
                ->whereHas('siswa', fn($s) => $s->where('cabang_id', $selectedCabangId))
                ->join('fees', 'payments.biaya_id', '=', 'fees.id')
                ->where(function($q) use ($terms) {
                    foreach ($terms as $term) {
                        $q->orWhere('fees.nama_biaya', 'LIKE', "%{$term}%");
                    }
                })
                ->sum('payments.nominal');
            
            $rekapIncome[] = ['keterangan' => $label, 'nominal' => $sum];
            $totalIncome += $sum;
        }

        // Others Income
        $otherIncome = Payment::where('payments.status', 'lunas')
            ->whereBetween('payments.tanggal_bayar', [$start, $end])
            ->whereHas('siswa', fn($s) => $s->where('cabang_id', $selectedCabangId))
            ->join('fees', 'payments.biaya_id', '=', 'fees.id')
            ->where(function($q) use ($categories) {
                foreach ($categories as $terms) {
                    foreach ($terms as $term) {
                        $q->where('fees.nama_biaya', 'NOT LIKE', "%{$term}%");
                    }
                }
            })
            ->sum('payments.nominal');
        
        $rekapIncome[] = ['keterangan' => 'Lain-lain', 'nominal' => $otherIncome];
        $totalIncome += $otherIncome;

        // EXPENSES (Operasional)
        $totalOperasional = Pengeluaran::whereBetween('tanggal', [$start, $end])
            ->where('cabang_id', $selectedCabangId)
            ->sum('nominal');

        // Total Expenses might include salaries too, but based on image, "Operasional" is the main one shown.
        // We'll follow the image structure.
        
        return view('modules.laporan-keuangan.rekap', [
            'month' => $month,
            'cabang' => $cabang,
            'rekapIncome' => $rekapIncome,
            'totalOperasional' => $totalOperasional,
            'totalIncome' => $totalIncome,
            'selectedCabangId' => $selectedCabangId
        ]);
    }

    public function bulanan(Request $request): View
    {
        $cabangId = $this->getCabangId();
        $selectedCabangId = $cabangId ?: $request->cabang_id;
        if (!$selectedCabangId) {
            return redirect()->route('laporan-keuangan.index')->withErrors(['cabang' => 'Pilih cabang untuk melihat laporan bulanan.']);
        }

        $month = $request->string('month', now()->format('Y-m'))->toString();
        $start = Carbon::parse($month)->startOfMonth();
        $end = Carbon::parse($month)->endOfMonth();
        $cabang = Cabang::find($selectedCabangId);

        // INCOME breakdown by Fee type
        $incomeBreakdown = Payment::where('payments.status', 'lunas')
            ->whereBetween('payments.tanggal_bayar', [$start, $end])
            ->whereHas('siswa', fn($s) => $s->where('cabang_id', $selectedCabangId))
            ->join('fees', 'payments.biaya_id', '=', 'fees.id')
            ->select('fees.nama_biaya', DB::raw('SUM(payments.nominal) as total'))
            ->groupBy('fees.nama_biaya')
            ->get();

        // EXPENSE breakdown by Category
        $expenseBreakdown = Pengeluaran::whereBetween('tanggal', [$start, $end])
            ->where('cabang_id', $selectedCabangId)
            ->join('kategori_pengeluarans', 'pengeluarans.kategori_id', '=', 'kategori_pengeluarans.id')
            ->select('kategori_pengeluarans.nama_kategori', DB::raw('SUM(pengeluarans.nominal) as total'))
            ->groupBy('kategori_pengeluarans.nama_kategori')
            ->get();

        // SALARIES (Honor Guru)
        $totalSalaries = Salary::where('periode', $month)
            ->whereHas('tutor', fn($t) => $t->where('cabang_id', $selectedCabangId))
            ->sum('total_gaji');

        $totalIncome = $incomeBreakdown->sum('total');
        $totalExpenses = $expenseBreakdown->sum('total') + $totalSalaries;
        $netProfit = $totalIncome - $totalExpenses;

        // Bagi Hasil Calculation
        $shares = [
            'investor_pct' => $cabang->profit_share_investor,
            'pusat_pct' => $cabang->profit_share_pusat,
            'investor_amount' => ($netProfit > 0) ? ($netProfit * $cabang->profit_share_investor / 100) : 0,
            'pusat_amount' => ($netProfit > 0) ? ($netProfit * $cabang->profit_share_pusat / 100) : 0,
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

    public function exportExcel(Request $request) { /* To be implemented */ }
    public function exportPdf(Request $request) { /* To be implemented */ }
}
