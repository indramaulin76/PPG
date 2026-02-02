<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Kelompok;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WilayahController extends Controller
{
    // ========== DESA ==========
    
    public function desaIndex()
    {
        $desas = Desa::withCount('kelompoks', 'jamaahs')
            ->orderBy('nama_desa')
            ->paginate(15);

        return Inertia::render('Wilayah/Desa/Index', [
            'desas' => $desas,
        ]);
    }

    public function desaStore(Request $request)
    {
        $validated = $request->validate([
            'nama_desa' => 'required|string|max:100|unique:desas',
            'kode_desa' => 'nullable|string|max:20',
        ]);

        Desa::create($validated);

        return back()->with('success', 'Desa berhasil ditambahkan.');
    }

    public function desaUpdate(Request $request, Desa $desa)
    {
        $validated = $request->validate([
            'nama_desa' => 'required|string|max:100|unique:desas,nama_desa,' . $desa->id,
            'kode_desa' => 'nullable|string|max:20',
        ]);

        $desa->update($validated);

        return back()->with('success', 'Desa berhasil diperbarui.');
    }

    public function desaDestroy(Desa $desa)
    {
        if ($desa->kelompoks()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus desa yang masih memiliki kelompok.');
        }

        $desa->delete();

        return back()->with('success', 'Desa berhasil dihapus.');
    }

    // ========== KELOMPOK ==========

    public function kelompokIndex(Request $request)
    {
        $query = Kelompok::with('desa')->withCount('jamaahs');

        if ($request->filled('desa_id')) {
            $query->where('desa_id', $request->desa_id);
        }

        $kelompoks = $query->orderBy('nama_kelompok')->paginate(15)->withQueryString();

        return Inertia::render('Wilayah/Kelompok/Index', [
            'kelompoks' => $kelompoks,
            'desas' => Desa::select('id', 'nama_desa')->orderBy('nama_desa')->get(),
            'filters' => $request->only('desa_id'),
        ]);
    }

    public function kelompokStore(Request $request)
    {
        $validated = $request->validate([
            'desa_id' => 'required|exists:desas,id',
            'nama_kelompok' => 'required|string|max:100',
        ]);

        Kelompok::create($validated);

        return back()->with('success', 'Kelompok berhasil ditambahkan.');
    }

    public function kelompokUpdate(Request $request, Kelompok $kelompok)
    {
        $validated = $request->validate([
            'desa_id' => 'required|exists:desas,id',
            'nama_kelompok' => 'required|string|max:100',
        ]);

        $kelompok->update($validated);

        return back()->with('success', 'Kelompok berhasil diperbarui.');
    }

    public function kelompokDestroy(Kelompok $kelompok)
    {
        if ($kelompok->jamaahs()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus kelompok yang masih memiliki jamaah.');
        }

        $kelompok->delete();

        return back()->with('success', 'Kelompok berhasil dihapus.');
    }

    // ========== API ==========

    public function getKelompoksByDesa(Desa $desa)
    {
        return response()->json(
            $desa->kelompoks()->select('id', 'nama_kelompok')->orderBy('nama_kelompok')->get()
        );
    }
}
