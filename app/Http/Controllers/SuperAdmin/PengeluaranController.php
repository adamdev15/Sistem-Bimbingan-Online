<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\KategoriPengeluaran;
use App\Models\Pengeluaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PengeluaranController extends Controller
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
        
        $query = Pengeluaran::with(['kategori', 'cabang', 'creator'])
            ->latest('tanggal');

        if ($cabangId) {
            $query->where('cabang_id', $cabangId);
        } elseif ($request->filled('cabang_id')) {
            $query->where('cabang_id', $request->cabang_id);
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('month')) {
            $month = Carbon::parse($request->month);
            $query->whereMonth('tanggal', $month->month)
                  ->whereYear('tanggal', $month->year);
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal', [$request->start_date, $request->end_date]);
        }

        $pengeluarans = $query->paginate(15)->withQueryString();
        $kategoris = KategoriPengeluaran::all();
        $cabangs = Cabang::all();

        return view('modules.pengeluaran.index', [
            'pengeluarans' => $pengeluarans,
            'kategoris' => $kategoris,
            'cabangs' => $cabangs,
            'cabangId' => $cabangId,
            'filters' => $request->only(['cabang_id', 'kategori_id', 'start_date', 'end_date', 'month'])
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori_id' => 'required|exists:kategori_pengeluarans,id',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'required|string',
            'cabang_id' => 'nullable|exists:cabangs,id',
        ]);

        $cabangId = $this->getCabangId() ?: $validated['cabang_id'];
        
        if (!$cabangId && Auth::user()->hasRole('super_admin')) {
             return back()->withErrors(['cabang_id' => 'Cabang harus dipilih untuk Super Admin.']);
        }

        Pengeluaran::create([
            'tanggal' => $validated['tanggal'],
            'kategori_id' => $validated['kategori_id'],
            'nominal' => $validated['nominal'],
            'keterangan' => $validated['keterangan'],
            'cabang_id' => $cabangId,
            'tipe' => 'pengeluaran',
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('pengeluaran.index')->with('status', 'Pengeluaran berhasil dicatat.');
    }

    public function update(Request $request, Pengeluaran $pengeluaran): RedirectResponse
    {
        $cabangId = $this->getCabangId();
        if ($cabangId && $pengeluaran->cabang_id !== $cabangId) {
            abort(403);
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kategori_id' => 'required|exists:kategori_pengeluarans,id',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'required|string',
            'cabang_id' => 'nullable|exists:cabangs,id',
        ]);

        $updateCabangId = $cabangId ?: ($validated['cabang_id'] ?? $pengeluaran->cabang_id);

        $pengeluaran->update([
            'tanggal' => $validated['tanggal'],
            'kategori_id' => $validated['kategori_id'],
            'nominal' => $validated['nominal'],
            'keterangan' => $validated['keterangan'],
            'cabang_id' => $updateCabangId,
        ]);

        return redirect()->route('pengeluaran.index')->with('status', 'Pengeluaran berhasil diperbarui.');
    }

    public function destroy(Pengeluaran $pengeluaran): RedirectResponse
    {
        $cabangId = $this->getCabangId();
        if ($cabangId && $pengeluaran->cabang_id !== $cabangId) {
            abort(403);
        }

        $pengeluaran->delete();

        return redirect()->route('pengeluaran.index')->with('status', 'Pengeluaran berhasil dihapus.');
    }

    public function printReport(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'cabang_id' => 'required_without_all:roles.admin_cabang|exists:cabangs,id',
        ]);

        $cabangId = $this->getCabangId() ?: $request->cabang_id;
        $cabang = Cabang::findOrFail($cabangId);
        $month = $request->month;
        $start = \Carbon\Carbon::parse($month)->startOfMonth();
        $end = \Carbon\Carbon::parse($month)->endOfMonth();

        $pengeluarans = Pengeluaran::with('kategori')
            ->where('cabang_id', $cabangId)
            ->whereBetween('tanggal', [$start, $end])
            ->oldest('tanggal')
            ->oldest('id')
            ->get();

        $totalRunning = 0;
        $data = $pengeluarans->map(function($p, $index) use (&$totalRunning) {
            $totalRunning += $p->nominal;
            return [
                'tanggal' => $p->tanggal,
                'keterangan' => $p->keterangan,
                'hal' => $index + 1,
                'nominal' => $p->nominal,
                'total' => $totalRunning
            ];
        });

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pengeluaran-laporan', [
            'cabang' => $cabang,
            'month' => $month,
            'data' => $data,
            'grandTotal' => $totalRunning
        ]);

        return $pdf->stream("Laporan-Pengeluaran-{$cabang->nama_cabang}-{$month}.pdf");
    }
}
