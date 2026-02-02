<?php

namespace App\Http\Controllers;

use App\Models\Jamaah;
use App\Models\Kelompok;
use App\Models\Desa;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JamaahController extends Controller
{
    /**
     * Display a listing of jamaah with server-side pagination, search and filters.
     */
    public function index(Request $request)
    {
        $query = Jamaah::with(['kelompok.desa']);

        // Search by name
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by desa
        if ($request->filled('desa_id')) {
            $query->byDesa($request->desa_id);
        }

        // Filter by kelompok
        if ($request->filled('kelompok_id')) {
            $query->byKelompok($request->kelompok_id);
        }

        // Filter by gender
        if ($request->filled('jenis_kelamin')) {
            $query->byGender($request->jenis_kelamin);
        }

        // Filter by marital status
        if ($request->filled('status_pernikahan')) {
            $query->byStatus($request->status_pernikahan);
        }

        // Filter by age category
        if ($request->filled('kategori_usia')) {
            $kategori = $request->kategori_usia;
            $ranges = [
                'BALITA' => [0, 5],
                'ANAK' => [6, 12],
                'REMAJA' => [13, 17],
                'PEMUDA' => [18, 40],
                'DEWASA' => [41, 60],
                'LANSIA' => [61, 150],
            ];
            if (isset($ranges[$kategori])) {
                $query->byUsia($ranges[$kategori][0], $ranges[$kategori][1]);
            }
        }

        $jamaahs = $query->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($jamaah) => [
                'id' => $jamaah->id,
                'nama_lengkap' => $jamaah->nama_lengkap,
                'jenis_kelamin' => $jamaah->jenis_kelamin,
                'age' => $jamaah->age,
                'kategori_usia' => $jamaah->kategori_usia,
                'status_pernikahan' => $jamaah->status_pernikahan,
                'no_telepon' => $jamaah->no_telepon,
                'desa' => $jamaah->kelompok?->desa?->nama_desa,
                'kelompok' => $jamaah->kelompok?->nama_kelompok,
            ]);

        return Inertia::render('Jamaah/Index', [
            'jamaahs' => $jamaahs,
            'filters' => $request->only(['search', 'desa_id', 'kelompok_id', 'jenis_kelamin', 'status_pernikahan', 'kategori_usia']),
            'desas' => Desa::select('id', 'nama_desa')->orderBy('nama_desa')->get(),
            'kelompoks' => Kelompok::select('id', 'desa_id', 'nama_kelompok')->orderBy('nama_kelompok')->get(),
        ]);
    }

    /**
     * Show the form for creating a new jamaah.
     */
    public function create()
    {
        return Inertia::render('Jamaah/Create', [
            'desas' => Desa::select('id', 'nama_desa')->orderBy('nama_desa')->get(),
            'kelompoks' => Kelompok::select('id', 'desa_id', 'nama_kelompok')->orderBy('nama_kelompok')->get(),
        ]);
    }

    /**
     * Store a newly created jamaah in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelompok_id' => 'required|exists:kelompoks,id',
            'nama_lengkap' => 'required|string|max:255',
            'tgl_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'status_pernikahan' => 'nullable|in:BELUM,MENIKAH,JANDA,DUDA',
            'pendidikan_aktivitas' => 'nullable|string|max:100',
            'no_telepon' => 'nullable|string|max:20',
            'role_dlm_keluarga' => 'nullable|in:KEPALA,ISTRI,ANAK,LAINNYA',
        ]);

        Jamaah::create($validated);

        return redirect()->route('jamaah.index')
            ->with('success', 'Data jamaah berhasil ditambahkan.');
    }

    /**
     * Display the specified jamaah.
     */
    public function show(Jamaah $jamaah)
    {
        $jamaah->load(['kelompok.desa', 'keluarga']);

        return Inertia::render('Jamaah/Show', [
            'jamaah' => [
                'id' => $jamaah->id,
                'nama_lengkap' => $jamaah->nama_lengkap,
                'jenis_kelamin' => $jamaah->jenis_kelamin,
                'tgl_lahir' => $jamaah->tgl_lahir?->format('Y-m-d'),
                'age' => $jamaah->age,
                'kategori_usia' => $jamaah->kategori_usia,
                'status_pernikahan' => $jamaah->status_pernikahan,
                'pendidikan_aktivitas' => $jamaah->pendidikan_aktivitas,
                'no_telepon' => $jamaah->no_telepon,
                'role_dlm_keluarga' => $jamaah->role_dlm_keluarga,
                'desa' => $jamaah->kelompok?->desa?->nama_desa,
                'kelompok' => $jamaah->kelompok?->nama_kelompok,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified jamaah.
     */
    public function edit(Jamaah $jamaah)
    {
        return Inertia::render('Jamaah/Edit', [
            'jamaah' => [
                'id' => $jamaah->id,
                'kelompok_id' => $jamaah->kelompok_id,
                'nama_lengkap' => $jamaah->nama_lengkap,
                'tgl_lahir' => $jamaah->tgl_lahir?->format('Y-m-d'),
                'jenis_kelamin' => $jamaah->jenis_kelamin,
                'status_pernikahan' => $jamaah->status_pernikahan,
                'pendidikan_aktivitas' => $jamaah->pendidikan_aktivitas,
                'no_telepon' => $jamaah->no_telepon,
                'role_dlm_keluarga' => $jamaah->role_dlm_keluarga,
            ],
            'desas' => Desa::select('id', 'nama_desa')->orderBy('nama_desa')->get(),
            'kelompoks' => Kelompok::select('id', 'desa_id', 'nama_kelompok')->orderBy('nama_kelompok')->get(),
        ]);
    }

    /**
     * Update the specified jamaah in storage.
     */
    public function update(Request $request, Jamaah $jamaah)
    {
        $validated = $request->validate([
            'kelompok_id' => 'required|exists:kelompoks,id',
            'nama_lengkap' => 'required|string|max:255',
            'tgl_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'status_pernikahan' => 'nullable|in:BELUM,MENIKAH,JANDA,DUDA',
            'pendidikan_aktivitas' => 'nullable|string|max:100',
            'no_telepon' => 'nullable|string|max:20',
            'role_dlm_keluarga' => 'nullable|in:KEPALA,ISTRI,ANAK,LAINNYA',
        ]);

        $jamaah->update($validated);

        return redirect()->route('jamaah.index')
            ->with('success', 'Data jamaah berhasil diperbarui.');
    }

    /**
     * Remove the specified jamaah from storage.
     */
    public function destroy(Jamaah $jamaah)
    {
        $jamaah->delete();

        return redirect()->route('jamaah.index')
            ->with('success', 'Data jamaah berhasil dihapus.');
    }
}
