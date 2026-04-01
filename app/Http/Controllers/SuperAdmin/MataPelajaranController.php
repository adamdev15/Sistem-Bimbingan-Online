<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MataPelajaranController extends Controller
{
    public function index(): View
    {
        return view('modules.mata-pelajaran.index', [
            'mataPelajarans' => MataPelajaran::query()->orderBy('nama')->paginate(15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:160'],
            'kode' => ['nullable', 'string', 'max:32'],
        ]);

        MataPelajaran::query()->create($data);

        return back()->with('status', 'Mata pelajaran ditambahkan.');
    }

    public function update(Request $request, MataPelajaran $mataPelajaran): RedirectResponse
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:160'],
            'kode' => ['nullable', 'string', 'max:32'],
        ]);

        $mataPelajaran->update($data);

        return back()->with('status', 'Mata pelajaran diperbarui.');
    }

    public function destroy(MataPelajaran $mataPelajaran): RedirectResponse
    {
        if ($mataPelajaran->jadwals()->exists()) {
            return back()->withErrors(['mata' => 'Tidak dapat menghapus: masih dipakai di jadwal.']);
        }

        $mataPelajaran->delete();

        return back()->with('status', 'Mata pelajaran dihapus.');
    }
}
