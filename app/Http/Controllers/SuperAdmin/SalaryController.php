<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Cabang;
use App\Models\Salary;
use App\Models\Tutor;
use App\Services\SuperAdmin\ManagementService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalaryController extends Controller
{
    public function __construct(private readonly ManagementService $service) {}

    public function index(Request $request): View
    {
        return view('modules.salaries.index', [
            'salaries' => $this->service->salaryIndex($request),
            'tutors' => $this->tutorsForSalaryForm(),
            'filters' => $request->only(['status', 'tutor_id']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'tutor_id' => ['required', 'exists:tutors,id'],
            'periode' => ['required', 'string', 'max:64'],
            'total_jam' => ['required', 'integer', 'min:0'],
            'total_gaji' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pending,dibayar,diterima'],
        ]);

        $this->guardTutorCabang((int) $data['tutor_id']);

        Salary::query()->create([
            'tutor_id' => $data['tutor_id'],
            'periode' => $data['periode'],
            'total_jam' => $data['total_jam'],
            'total_gaji' => $data['total_gaji'],
            'status' => $data['status'] ?? 'pending',
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('status', 'Data gaji disimpan.');
    }

    public function update(Request $request, Salary $salary): RedirectResponse
    {
        $this->guardSalaryCabang($salary);

        $data = $request->validate([
            'status' => ['required', 'in:pending,dibayar,diterima'],
        ]);

        $salary->update($data);

        return back()->with('status', 'Status gaji diperbarui.');
    }

    private function tutorsForSalaryForm(): Collection
    {
        $cabangId = Cabang::query()->where('user_id', auth()->id())->value('id');

        return Tutor::query()
            ->select('id', 'nama', 'cabang_id')
            ->when(
                auth()->user()?->hasRole('admin_cabang') && $cabangId,
                fn ($q) => $q->where('cabang_id', $cabangId)
            )
            ->orderBy('nama')
            ->get();
    }

    private function guardTutorCabang(int $tutorId): void
    {
        $user = auth()->user();
        if (! $user || $user->hasRole('super_admin')) {
            return;
        }

        if (! $user->hasRole('admin_cabang')) {
            abort(403);
        }

        $adminCabangId = Cabang::query()->where('user_id', $user->id)->value('id');
        $tutorCabang = Tutor::query()->whereKey($tutorId)->value('cabang_id');
        if ((int) $adminCabangId !== (int) $tutorCabang) {
            abort(403);
        }
    }

    private function guardSalaryCabang(Salary $salary): void
    {
        $user = auth()->user();
        if (! $user || $user->hasRole('super_admin')) {
            return;
        }

        if (! $user->hasRole('admin_cabang')) {
            abort(403);
        }

        $adminCabangId = Cabang::query()->where('user_id', $user->id)->value('id');
        $tutorCabang = $salary->tutor?->cabang_id;
        if ((int) $adminCabangId !== (int) $tutorCabang) {
            abort(403);
        }
    }
}
