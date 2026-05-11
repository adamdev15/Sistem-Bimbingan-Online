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
            'prices.*.id' => ['required', 'exists:branch_materi_prices,id'],
            'prices.*.biaya_daftar' => ['required', 'numeric', 'min:0'],
            'prices.*.biaya_spp' => ['required', 'numeric', 'min:0'],
        ]);

        foreach ($data['prices'] as $priceData) {
            $price = BranchMateriPrice::find($priceData['id']);
            
            // Security check for Branch Admin
            if (auth()->user()->hasRole('admin_cabang')) {
                $myCabangId = DB::table('cabangs')->where('user_id', auth()->id())->value('id');
                if ($price->cabang_id != $myCabangId) {
                    continue;
                }
            }

            $price->update([
                'biaya_daftar' => $priceData['biaya_daftar'],
                'biaya_spp' => $priceData['biaya_spp'],
            ]);
        }

        return back()->with('success', 'Harga materi berhasil diperbarui.');
    }
}
