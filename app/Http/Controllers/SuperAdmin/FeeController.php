<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $fees = Fee::latest()->paginate(10);
        return view('modules.fees.index', compact('fees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_biaya' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:65535',
            'tipe' => 'required|in:bulanan,sekali',
        ]);

        Fee::create($validated);

        return redirect()->route('fees.index')->with('success', 'Biaya berhasil ditambahkan.');
    }

    public function update(Request $request, Fee $fee)
    {
        $validated = $request->validate([
            'nama_biaya' => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:65535',
            'tipe' => 'required|in:bulanan,sekali',
        ]);

        $fee->update($validated);

        return redirect()->route('fees.index')->with('success', 'Biaya berhasil diperbarui.');
    }

    public function destroy(Fee $fee)
    {
        $fee->delete();
        return redirect()->route('fees.index')->with('success', 'Biaya berhasil dihapus.');
    }
}
