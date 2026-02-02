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
        // Total counts
        $totalJamaah = Jamaah::count();
        $totalKK = Jamaah::whereNotNull('keluarga_id')->distinct('keluarga_id')->count('keluarga_id');
        $totalDesa = Desa::count();

        // Gender ratio
        $genderCounts = Jamaah::select('jenis_kelamin', DB::raw('count(*) as total'))
            ->groupBy('jenis_kelamin')
            ->pluck('total', 'jenis_kelamin');
        
        $maleCount = $genderCounts['L'] ?? 0;
        $femaleCount = $genderCounts['P'] ?? 0;
        $totalGender = $maleCount + $femaleCount;
        $malePercent = $totalGender > 0 ? round(($maleCount / $totalGender) * 100) : 50;
        $femalePercent = 100 - $malePercent;

        // Age distribution
        $now = Carbon::now();
        $ageDistribution = [
            'BALITA' => Jamaah::whereBetween('tgl_lahir', [$now->copy()->subYears(5), $now])->count(),
            'ANAK' => Jamaah::whereBetween('tgl_lahir', [$now->copy()->subYears(12), $now->copy()->subYears(6)])->count(),
            'REMAJA' => Jamaah::whereBetween('tgl_lahir', [$now->copy()->subYears(17), $now->copy()->subYears(13)])->count(),
            'PEMUDA' => Jamaah::whereBetween('tgl_lahir', [$now->copy()->subYears(40), $now->copy()->subYears(18)])->count(),
            'DEWASA' => Jamaah::whereBetween('tgl_lahir', [$now->copy()->subYears(60), $now->copy()->subYears(41)])->count(),
            'LANSIA' => Jamaah::where('tgl_lahir', '<', $now->copy()->subYears(60))->count(),
        ];

        // Marital status distribution
        $maritalDistribution = Jamaah::select('status_pernikahan', DB::raw('count(*) as total'))
            ->whereNotNull('status_pernikahan')
            ->groupBy('status_pernikahan')
            ->pluck('total', 'status_pernikahan')
            ->toArray();

        // Recent data (5 terbaru)
        $recentJamaah = Jamaah::with(['kelompok.desa'])
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

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalJamaah' => $totalJamaah,
                'totalKK' => $totalKK,
                'totalDesa' => $totalDesa,
                'genderRatio' => "{$malePercent}:{$femalePercent}",
            ],
            'ageDistribution' => $ageDistribution,
            'maritalDistribution' => $maritalDistribution,
            'recentJamaah' => $recentJamaah,
        ]);
    }
}
