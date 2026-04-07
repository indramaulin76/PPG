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
            'nama_desa' => 'required|string|max:100|unique:desas,nama_desa,'.$desa->id,
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
        $user = auth()->user();
        $query = Kelompok::with('desa')->withCount('jamaahs');

        if ($user->isAdminDesa()) {
            $query->where('desa_id', $user->desa_id);
        } elseif ($request->filled('desa_id')) {
            $query->where('desa_id', $request->desa_id);
        }

        $kelompoks = $query->orderBy('nama_kelompok')->paginate(15)->withQueryString();

        $desas = $user->isAdminDesa()
            ? Desa::where('id', $user->desa_id)->select('id', 'nama_desa')->get()
            : Desa::select('id', 'nama_desa')->orderBy('nama_desa')->get();

        return Inertia::render('Wilayah/Kelompok/Index', [
            'kelompoks' => $kelompoks,
            'desas' => $desas,
            'filters' => $request->only('desa_id'),
            'isAdminDesa' => $user->isAdminDesa(),
        ]);
    }

    public function kelompokStore(Request $request)
    {
        $user = auth()->user();

        if ($user->isAdminDesa() && $request->desa_id != $user->desa_id) {
            abort(403, 'Anda hanya bisa menambahkan kelompok di desa Anda sendiri.');
        }

        $validated = $request->validate([
            'desa_id' => 'required|exists:desas,id',
            'nama_kelompok' => 'required|string|max:100',
        ]);

        Kelompok::create($validated);

        return back()->with('success', 'Kelompok berhasil ditambahkan.');
    }

    public function kelompokUpdate(Request $request, Kelompok $kelompok)
    {
        $user = auth()->user();

        if ($user->isAdminDesa() && $kelompok->desa_id !== $user->desa_id) {
            abort(403, 'Anda tidak memiliki akses ke kelompok ini');
        }

        $validated = $request->validate([
            'nama_kelompok' => 'required|string|max:100',
        ]);

        $kelompok->update($validated);

        return back()->with('success', 'Kelompok berhasil diperbarui.');
    }

    public function kelompokDestroy(Kelompok $kelompok)
    {
        $user = auth()->user();

        if ($user->isAdminDesa() && $kelompok->desa_id !== $user->desa_id) {
            abort(403, 'Anda tidak memiliki akses ke kelompok ini.');
        }

        if (! $user->isSuperAdmin() && ! $user->isAdminDesa()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus kelompok.');
        }

        if ($kelompok->jamaahs()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus kelompok yang masih memiliki jamaah.');
        }

        $kelompok->delete();

        return back()->with('success', 'Kelompok berhasil dihapus.');
    }

    // ========== API ==========

    public function getKelompoksByDesa(Desa $desa)
    {
        $user = auth()->user();

        if ($user->isAdminDesa() && $desa->id !== $user->desa_id) {
            abort(403, 'Anda tidak memiliki akses ke desa ini.');
        }

        return response()->json(
            $desa->kelompoks()->select('id', 'nama_kelompok')->orderBy('nama_kelompok')->get()
        );
    }
}
