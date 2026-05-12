<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\BranchMateriPrice;
use App\Models\Cabang;
use App\Models\MateriLes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BranchPriceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Cabang::query();

        if ($user->hasRole('admin_cabang')) {
            $cabangId = DB::table('cabangs')->where('user_id', $user->id)->value('id');
            $query->where('id', $cabangId);
        }

        $cabangs = $query->with(['branchPrices.materiLes'])->get();
        $materiLes = MateriLes::all();

        return view('modules.branch-prices.index', compact('cabangs', 'materiLes'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*.id' => ['nullable'],
            'prices.*.materi_les_id' => ['required', 'exists:materi_les,id'],
            'prices.*.biaya_daftar' => ['required', 'numeric', 'min:0'],
            'prices.*.biaya_spp' => ['required', 'numeric', 'min:0'],
        ]);

        $user = auth()->user();
        $myCabangId = null;
        if ($user->hasRole('admin_cabang')) {
            $myCabangId = DB::table('cabangs')->where('user_id', $user->id)->value('id');
        }

        foreach ($data['prices'] as $materiId => $priceData) {
            $cabangId = $myCabangId;

            // If super_admin, we might need to get cabang_id from the record if id exists
            if (!$cabangId && !empty($priceData['id'])) {
                $cabangId = BranchMateriPrice::where('id', $priceData['id'])->value('cabang_id');
            }

            if (!$cabangId) continue;

            BranchMateriPrice::updateOrCreate(
                [
                    'cabang_id' => $cabangId,
                    'materi_les_id' => $priceData['materi_les_id'],
                ],
                [
                    'biaya_daftar' => $priceData['biaya_daftar'],
                    'biaya_spp' => $priceData['biaya_spp'],
                ]
            );
        }

        return back()->with('success', 'Harga materi berhasil diperbarui.');
    }
}
