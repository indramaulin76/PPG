<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JamaahController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
require __DIR__.'/auth.php';

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Jamaah CRUD
    Route::resource('jamaah', JamaahController::class);

    // Wilayah - Desa
    Route::prefix('wilayah')->name('wilayah.')->group(function () {
        Route::get('/desa', [WilayahController::class, 'desaIndex'])->name('desa.index');
        Route::post('/desa', [WilayahController::class, 'desaStore'])->name('desa.store');
        Route::put('/desa/{desa}', [WilayahController::class, 'desaUpdate'])->name('desa.update');
        Route::delete('/desa/{desa}', [WilayahController::class, 'desaDestroy'])->name('desa.destroy');

        // Kelompok
        Route::get('/kelompok', [WilayahController::class, 'kelompokIndex'])->name('kelompok.index');
        Route::post('/kelompok', [WilayahController::class, 'kelompokStore'])->name('kelompok.store');
        Route::put('/kelompok/{kelompok}', [WilayahController::class, 'kelompokUpdate'])->name('kelompok.update');
        Route::delete('/kelompok/{kelompok}', [WilayahController::class, 'kelompokDestroy'])->name('kelompok.destroy');
    });

    // API: Get kelompoks by desa (for dynamic dropdown)
    Route::get('/api/desa/{desa}/kelompoks', [WilayahController::class, 'getKelompoksByDesa'])->name('api.kelompoks');

    // Import CSV
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/', [ImportController::class, 'import'])->name('store');
        Route::get('/template', [ImportController::class, 'downloadTemplate'])->name('template');
    });

    // Export CSV
    Route::get('/export/jamaah', [ImportController::class, 'export'])->name('export.jamaah');
}); // End of auth middleware group
