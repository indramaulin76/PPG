<?php

namespace App\Http\Controllers;

use App\Models\Jamaah;
use App\Models\Desa;
use App\Models\Kelompok;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // ===== SCOPE DATA BASED ON ROLE =====
        $jamaahQuery = Jamaah::query();
        
        if ($user->isAdminDesa()) {
            // Admin Desa: only see data from their desa
            $jamaahQuery->whereHas('kelompok', fn($q) => $q->where('desa_id', $user->desa_id));
        } elseif ($user->isAdminKelompok()) {
            // Admin Kelompok: only see data from their kelompok
            $jamaahQuery->where('kelompok_id', $user->kelompok_id);
        }
        // Super Admin: no filter, sees everything
        
        // Total counts (scoped)
        $totalJamaah = $jamaahQuery->count();
        $totalKK = (clone $jamaahQuery)->whereNotNull('keluarga_id')->distinct('keluarga_id')->count('keluarga_id');
        $totalDesa = $user->isSuperAdmin() ? Desa::count() : 1;
        
        $totalKelompok = match(true) {
            $user->isSuperAdmin() => Kelompok::count(),
            $user->isAdminDesa() => Kelompok::where('desa_id', $user->desa_id)->count(),
            $user->isAdminKelompok() => 1,
            default => 0,
        };

        // Gender ratio (scoped)
        $genderCounts = (clone $jamaahQuery)->select('jenis_kelamin', DB::raw('count(*) as total'))
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');
        
        $maleCount = $genderCounts['L'] ?? 0;
        $femaleCount = $genderCounts['P'] ?? 0;
        $totalGender = $maleCount + $femaleCount;
        $malePercent = $totalGender > 0 ? round(($maleCount / $totalGender) * 100) : 50;
        $femalePercent = 100 - $malePercent;

        // Age distribution (scoped)
        $ageDistribution = (clone $jamaahQuery)->selectRaw('
            CASE 
                WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) <= 5 THEN "BALITA"
                WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) <= 12 THEN "ANAK"
                WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) <= 17 THEN "REMAJA"
                WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) <= 40 THEN "PEMUDA"
                WHEN TIMESTAMPDIFF(YEAR, tgl_lahir, CURDATE()) <= 60 THEN "DEWASA"
                WHEN tgl_lahir IS NOT NULL THEN "LANSIA"
                ELSE "TIDAK_DIKETAHUI"
            END as age_group, COUNT(*) as count')
            ->whereNotNull('tgl_lahir')
            ->groupBy('age_group')
            ->pluck('count', 'age_group')
            ->toArray();

        // Marital status distribution (scoped)
        $maritalDistribution = (clone $jamaahQuery)->select('status_pernikahan', DB::raw('count(*) as total'))
            ->whereNotNull('status_pernikahan')
            ->groupBy('status_pernikahan')
            ->pluck('total', 'status_pernikahan')
            ->toArray();

        // Recent data (5 terbaru, scoped)
        $recentJamaah = (clone $jamaahQuery)->with(['kelompok.desa'])
            ->latest()
            ->take(5)
            ->get()
            ->map(fn ($j) => [
                'id' => $j->id,
                'name' => $j->nama_lengkap,
                'desa' => $j->kelompok?->desa?->nama_desa ?? '-',
                'kelompok' => $j->kelompok?->nama_kelompok ?? '-',
                'status' => $j->status_pernikahan ?? 'BELUM',
                'umur' => $j->age,
            ]);

        // Determine view based on role
        $view = match(true) {
            $user->isSuperAdmin() => 'Dashboard/SuperAdmin',
            $user->isAdminDesa() => 'Dashboard/AdminDesa',
            $user->isAdminKelompok() => 'Dashboard/AdminKelompok',
            default => 'Dashboard',
        };

        return Inertia::render($view, [
            'stats' => [
                'totalJamaah' => $totalJamaah,
                'totalKK' => $totalKK,
                'totalDesa' => $totalDesa,
                'totalKelompok' => $totalKelompok,
                'genderRatio' => "{$malePercent}:{$femalePercent}",
            ],
            'ageDistribution' => $ageDistribution,
            'maritalDistribution' => $maritalDistribution,
            'recentJamaah' => $recentJamaah,
        ]);
    }
}
