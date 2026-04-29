<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\MateriLes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MateriLesController extends Controller
{
    public function index(Request $request)
    {
        $materiLes = MateriLes::query()
            ->when($request->search, function ($query, $search) {
                $query->where('nama_materi', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return view('modules.materi-les.index', compact('materiLes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_materi' => ['required', 'string', 'max:255', 'unique:materi_les,nama_materi'],
            'deskripsi' => ['nullable', 'string'],
            'pertemuan_per_minggu' => ['required', 'integer', 'min:1'],
            'biaya_daftar' => ['nullable', 'numeric', 'min:0'],
            'biaya_spp' => ['nullable', 'numeric', 'min:0'],
            'biaya_tutor' => ['nullable', 'numeric', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('image/materi'), $filename);
            $validated['foto'] = $filename;
        }

        MateriLes::create($validated);

        return redirect()->route('materi-les.index')->with('success', 'Materi les berhasil ditambahkan.');
    }

    public function update(Request $request, MateriLes $materiLes)
    {
        $validated = $request->validate([
            'nama_materi' => [
                'required',
                'string',
                'max:255',
                Rule::unique('materi_les')->ignore($materiLes->id),
            ],
            'deskripsi' => ['nullable', 'string'],
            'pertemuan_per_minggu' => ['required', 'integer', 'min:1'],
            'biaya_daftar' => ['nullable', 'numeric', 'min:0'],
            'biaya_spp' => ['nullable', 'numeric', 'min:0'],
            'biaya_tutor' => ['nullable', 'numeric', 'min:0'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('image/materi'), $filename);
            $validated['foto'] = $filename;

            // Delete old photo if exists
            if ($materiLes->foto && file_exists(public_path('image/materi/' . $materiLes->foto))) {
                unlink(public_path('image/materi/' . $materiLes->foto));
            }
        }

        $materiLes->update($validated);

        return redirect()->route('materi-les.index')->with('success', 'Materi les berhasil diperbarui.');
    }

    public function destroy(MateriLes $materiLes)
    {
        if ($materiLes->foto && file_exists(public_path('image/materi/' . $materiLes->foto))) {
            unlink(public_path('image/materi/' . $materiLes->foto));
        }
        $materiLes->delete();

        return redirect()->route('materi-les.index')->with('success', 'Materi les berhasil dihapus.');
    }
}
