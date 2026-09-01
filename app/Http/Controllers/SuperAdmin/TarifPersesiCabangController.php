<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TarifPersesiCabangController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = \App\Models\Cabang::query();

        if ($user->hasRole('admin_cabang')) {
            $cabangId = \Illuminate\Support\Facades\DB::table('cabangs')->where('user_id', $user->id)->value('id');
            $query->where('id', $cabangId);
        }

        $cabangs = $query->with(['tarifPersesiCabang'])->get();

        return view('modules.tarif-persesi-cabang.index', compact('cabangs'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*.id' => ['nullable'],
            'prices.*.nominal' => ['required', 'numeric', 'min:0'],
        ]);

        $user = auth()->user();
        $myCabangId = null;
        if ($user->hasRole('admin_cabang')) {
            $myCabangId = \Illuminate\Support\Facades\DB::table('cabangs')->where('user_id', $user->id)->value('id');
        }

        foreach ($data['prices'] as $cabangId => $priceData) {
            $targetCabangId = $myCabangId ?: $cabangId;

            \App\Models\TarifPersesiCabang::updateOrCreate(
                [
                    'cabang_id' => $targetCabangId,
                ],
                [
                    'nominal' => $priceData['nominal'],
                ]
            );
        }

        return back()->with('status', 'Tarif persesi cabang berhasil diperbarui.');
    }
}
