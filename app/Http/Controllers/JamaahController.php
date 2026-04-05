<?php

namespace App\Http\Controllers;

use App\Models\Jamaah;
use App\Models\Kelompok;
use App\Models\Desa;
use App\Http\Requests\JamaahStoreRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JamaahController extends Controller
{
    /**
     * Display a listing of jamaah with server-side pagination, search and filters.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Jamaah::with(['kelompok.desa']);

       // ===== AUTO-SCOPE BASED ON ROLE =====
        if ($user->isAdminDesa()) {
            $query->whereHas('kelompok', fn($q) => $q->where('desa_id', $user->desa_id));
        } elseif ($user->isAdminKelompok()) {
            $query->where('kelompok_id', $user->kelompok_id);
        }

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

        // Filter by Paket (groups of kelas_generus)
        if ($request->filled('paket')) {
            $paket = $request->paket;
            $paketMapping = [
                'PAUD' => ['PAUD'],
                'A' => ['KELAS 1', 'KELAS 2', 'KELAS 3'],
                'B' => ['KELAS 4', 'KELAS 5', 'KELAS 6'],
                'C' => ['KELAS 7', 'KELAS 8', 'KELAS 9'],
                'D' => ['KELAS 10', 'KELAS 11', 'KELAS 12'],
                'PRA_NIKAH' => ['MUDA-MUDI'],
            ];

            if ($paket === 'UMUM') {
                // Umum = jamaah yang sudah menikah / janda / duda
                $query->whereIn('status_pernikahan', ['MENIKAH', 'JANDA', 'DUDA']);
            } elseif (isset($paketMapping[$paket])) {
                $query->whereIn('kelas_generus', $paketMapping[$paket]);
            }
        }

        // Filter by kategori sodaqoh
        if ($request->filled('kategori_sodaqoh')) {
            $query->where('kategori_sodaqoh', $request->kategori_sodaqoh);
        }

        // Filter by status mubaligh
        if ($request->filled('status_mubaligh')) {
            $query->where('status_mubaligh', $request->status_mubaligh);
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
                'tempat_lahir' => $jamaah->tempat_lahir,
                'tgl_lahir' => $jamaah->tgl_lahir?->format('d/m/Y'),
                'jenis_kelamin' => $jamaah->jenis_kelamin,
                'age' => $jamaah->age,
                'kategori_usia' => $jamaah->kategori_usia,
                'kelas_generus' => $jamaah->kelas_generus,
                'status_pernikahan' => $jamaah->status_pernikahan,
                'kategori_sodaqoh' => $jamaah->kategori_sodaqoh,
                'dapukan' => $jamaah->dapukan,
                'pekerjaan' => $jamaah->pekerjaan,
                'status_mubaligh' => $jamaah->status_mubaligh,
                'pendidikan_terakhir' => $jamaah->pendidikan_terakhir,
                'minat_kbm' => $jamaah->minat_kbm,
                'no_telepon' => $jamaah->no_telepon,
                'desa' => $jamaah->kelompok?->desa?->nama_desa,
                'kelompok' => $jamaah->kelompok?->nama_kelompok,
            ]);

        return Inertia::render('Jamaah/Index', [
            'jamaahs' => $jamaahs,
            'filters' => $request->only(['search', 'desa_id', 'kelompok_id', 'jenis_kelamin', 'status_pernikahan', 'kategori_usia', 'paket', 'kategori_sodaqoh', 'status_mubaligh']),
            'desas' => Desa::select('id', 'nama_desa')->orderBy('nama_desa')->get(),
            'kelompoks' => Kelompok::select('id', 'desa_id', 'nama_kelompok')->orderBy('nama_kelompok')->get(),
            'dropdowns' => [
                'kategori_sodaqoh' => Jamaah::KATEGORI_SODAQOH,
                'status_mubaligh' => Jamaah::STATUS_MUBALIGH,
            ],
        ]);
    }

    /**
     * Show the form for creating a new jamaah.
     */
    public function create()
    {
        $user = auth()->user();
        
        // Scope desas and kelompoks based on role
        $desas = $user->isSuperAdmin() 
            ? Desa::select('id', 'nama_desa')->orderBy('nama_desa')->get()
            : Desa::where('id', $user->desa_id)->get();
        
        $kelompoks = $user->isSuperAdmin()
            ? Kelompok::select('id', 'desa_id', 'nama_kelompok')->orderBy('nama_kelompok')->get()
            : Kelompok::where('desa_id', $user->desa_id)->select('id', 'desa_id', 'nama_kelompok')->get();
        
        return Inertia::render('Jamaah/Create', [
            'desas' => $desas,
            'kelompoks' => $kelompoks,
            'dropdowns' => [
                'status_pernikahan' => Jamaah::STATUS_PERNIKAHAN,
                'kelas_generus' => Jamaah::KELAS_GENERUS,
                'kategori_sodaqoh' => Jamaah::KATEGORI_SODAQOH,
                'status_mubaligh' => Jamaah::STATUS_MUBALIGH,
                'pendidikan' => Jamaah::PENDIDIKAN,
                'dapukan' => Jamaah::DAPUKAN,
                'pekerjaan' => Jamaah::PEKERJAAN_OPTIONS,
                'minat_kbm' => Jamaah::MINAT_KBM,
            ],
        ]);
    }

    /**
     * Store a newly created jamaah in storage.
     */
    public function store(JamaahStoreRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();

        // Security: Validate kelompok access
        $kelompok = Kelompok::find($validated['kelompok_id']);
        if ($user->isAdminDesa() && $kelompok->desa_id !== $user->desa_id) {
            abort(403, 'Anda tidak bisa menambahkan jamaah di desa lain');
        } elseif ($user->isAdminKelompok() && $validated['kelompok_id'] !== $user->kelompok_id) {
            abort(403, 'Anda tidak bisa menambahkan jamaah di kelompok lain');
        }

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
                'tempat_lahir' => $jamaah->tempat_lahir,
                'tgl_lahir' => $jamaah->tgl_lahir?->format('Y-m-d'),
                'age' => $jamaah->age,
                'jenis_kelamin' => $jamaah->jenis_kelamin,
                'kategori_usia' => $jamaah->kategori_usia,
                'status_pernikahan' => $jamaah->status_pernikahan,
                'role_dlm_keluarga' => $jamaah->role_dlm_keluarga,
                'no_telepon' => $jamaah->no_telepon,
                'desa' => $jamaah->kelompok?->desa?->nama_desa,
                'kelompok' => $jamaah->kelompok?->nama_kelompok,
                // New fields from Plan.md
                'kelas_generus' => $jamaah->kelas_generus,
                'status_mubaligh' => $jamaah->status_mubaligh,
                'kategori_sodaqoh' => $jamaah->kategori_sodaqoh,
                'pendidikan_terakhir' => $jamaah->pendidikan_terakhir,
                'pekerjaan' => $jamaah->pekerjaan,
                'dapukan' => $jamaah->dapukan,
                'minat_kbm' => $jamaah->minat_kbm,
                'pendidikan_aktivitas' => $jamaah->pendidikan_aktivitas,
            ],
        ]);
    }

    /**
     * Show the form for editing the specified jamaah.
     */
    public function edit(Jamaah $jamaah)
    {
        $user = auth()->user();
        
        // Security: Check access to this jamaah
        if ($user->isAdminDesa() && $jamaah->kelompok->desa_id !== $user->desa_id) {
            abort(403, 'Anda tidak bisa mengedit jamaah di desa lain');
        } elseif ($user->isAdminKelompok() && $jamaah->kelompok_id !== $user->kelompok_id) {
            abort(403, 'Anda tidak bisa mengedit jamaah di kelompok lain');
        }
        
        // Scope dropdown options
        $desas = $user->isSuperAdmin()
            ? Desa::select('id', 'nama_desa')->orderBy('nama_desa')->get()
            : Desa::where('id', $user->desa_id)->get();
        
        $kelompoks = $user->isSuperAdmin()
            ? Kelompok::select('id', 'desa_id', 'nama_kelompok')->orderBy('nama_kelompok')->get()
            : Kelompok::where('desa_id', $user->desa_id)->select('id', 'desa_id', 'nama_kelompok')->get();
        
        return Inertia::render('Jamaah/Edit', [
            'jamaah' => [
                'id' => $jamaah->id,
                'kelompok_id' => $jamaah->kelompok_id,
                'nama_lengkap' => $jamaah->nama_lengkap,
                'tempat_lahir' => $jamaah->tempat_lahir,
                'tgl_lahir' => $jamaah->tgl_lahir?->format('Y-m-d'),
                'jenis_kelamin' => $jamaah->jenis_kelamin,
                'kelas_generus' => $jamaah->kelas_generus,
                'status_pernikahan' => $jamaah->status_pernikahan,
                'kategori_sodaqoh' => $jamaah->kategori_sodaqoh,
                'dapukan' => $jamaah->dapukan,
                'pekerjaan' => $jamaah->pekerjaan,
                'status_mubaligh' => $jamaah->status_mubaligh,
                'pendidikan_terakhir' => $jamaah->pendidikan_terakhir,
                'minat_kbm' => $jamaah->minat_kbm,
                'pendidikan_aktivitas' => $jamaah->pendidikan_aktivitas,
                'no_telepon' => $jamaah->no_telepon,
                'role_dlm_keluarga' => $jamaah->role_dlm_keluarga,
            ],
            'desas' => $desas,
            'kelompoks' => $kelompoks,
            'dropdowns' => [
                'status_pernikahan' => Jamaah::STATUS_PERNIKAHAN,
                'kelas_generus' => Jamaah::KELAS_GENERUS,
                'kategori_sodaqoh' => Jamaah::KATEGORI_SODAQOH,
                'status_mubaligh' => Jamaah::STATUS_MUBALIGH,
                'pendidikan' => Jamaah::PENDIDIKAN,
                'dapukan' => Jamaah::DAPUKAN,
                'pekerjaan' => Jamaah::PEKERJAAN_OPTIONS,
                'minat_kbm' => Jamaah::MINAT_KBM,
            ],
        ]);
    }

    /**
     * Update the specified jamaah in storage.
     */
    public function update(Request $request, Jamaah $jamaah)
    {
        $validated = $request->validate([
            'kelompok_id' => 'required|exists:kelompoks,id',
            'keluarga_id' => 'nullable|exists:keluargas,id',
            'nama_lengkap' => 'required|string|max:255|regex:/^[a-zA-Z\s\.\-\'\.]+$/',
            'tgl_lahir' => 'nullable|date|before:today|after:1900-01-01',
            'jenis_kelamin' => 'required|in:L,P',
            'status_pernikahan' => ['nullable', \Illuminate\Validation\Rule::in(\App\Models\Jamaah::STATUS_PERNIKAHAN)],
            'pendidikan_aktivitas' => 'nullable|string|max:100|regex:/^[a-zA-Z0-9\s\.\-]+$/',
            'no_telepon' => 'nullable|string|max:20|regex:/^[0-9\+\-\s\(\)]+$/',
            'role_dlm_keluarga' => ['nullable', \Illuminate\Validation\Rule::in(['KEPALA', 'ISTRI', 'ANAK', 'LAINNYA'])],
            
            // Additional fields from original update method that weren't in store request
            'tempat_lahir' => 'nullable|string|max:255',
            'kelas_generus' => 'nullable|string|max:50',
            'kategori_sodaqoh' => 'nullable|string|max:50',
            'dapukan' => 'nullable|string|max:100',
            'pekerjaan' => 'nullable|string|max:100',
            'status_mubaligh' => 'nullable|string|max:50',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'minat_kbm' => 'nullable|string|max:255',
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
        $user = auth()->user();
        
        // Security: Check access to delete this jamaah
        if ($user->isAdminDesa() && $jamaah->kelompok->desa_id !== $user->desa_id) {
            abort(403, 'Anda tidak bisa menghapus jamaah di desa lain');
        } elseif ($user->isAdminKelompok() && $jamaah->kelompok_id !== $user->kelompok_id) {
            abort(403, 'Anda tidak bisa menghapus jamaah di kelompok lain');
        }
        
        $jamaah->delete();

        return redirect()->route('jamaah.index')
            ->with('success', 'Data jamaah berhasil dihapus.');
    }

    /**
     * Remove all jamaahs from storage (Super Admin Only).
     */
    public function destroyAll()
    {
        $user = auth()->user();

        if (!$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Super Admin.');
        }

        Jamaah::truncate();
        
        return redirect()->route('jamaah.index')->with('success', 'Seluruh data jamaah berhasil dikosongkan!');
    }
}
