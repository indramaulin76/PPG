<?php

use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\JamaahController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
require __DIR__.'/auth.php';

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard - all authenticated users
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Jamaah CRUD - all admin levels can access
    Route::resource('jamaah', JamaahController::class);

    // Wilayah Management
    Route::prefix('wilayah')->name('wilayah.')->group(function () {
        // Desa - only Super Admin can modify
        Route::middleware(['role:super_admin'])->group(function () {
            Route::get('/desa', [WilayahController::class, 'desaIndex'])->name('desa.index');
            Route::post('/desa', [WilayahController::class, 'desaStore'])->name('desa.store');
            Route::put('/desa/{desa}', [WilayahController::class, 'desaUpdate'])->name('desa.update');
            Route::delete('/desa/{desa}', [WilayahController::class, 'desaDestroy'])->name('desa.destroy');
        });

        // Kelompok - Super Admin & Admin Desa can manage
        Route::middleware(['role:super_admin,admin_desa'])->group(function () {
            Route::get('/kelompok', [WilayahController::class, 'kelompokIndex'])->name('kelompok.index');
            Route::post('/kelompok', [WilayahController::class, 'kelompokStore'])->name('kelompok.store');
            Route::put('/kelompok/{kelompok}', [WilayahController::class, 'kelompokUpdate'])->name('kelompok.update');
            Route::delete('/kelompok/{kelompok}', [WilayahController::class, 'kelompokDestroy'])->name('kelompok.destroy');
        });
    });

    // Admin Management - Super Admin & Admin Desa only
    Route::middleware(['role:super_admin,admin_desa'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            Route::get('/', [AdminManagementController::class, 'index'])->name('index');
            Route::post('/', [AdminManagementController::class, 'store'])->name('store');
            Route::put('/{admin}', [AdminManagementController::class, 'update'])->name('update');
            Route::delete('/{admin}', [AdminManagementController::class, 'destroy'])->name('destroy');
        });

    // API: Get kelompoks by desa (for dynamic dropdown)
    Route::get('/api/desa/{desa}/kelompoks', [WilayahController::class, 'getKelompoksByDesa'])->name('api.kelompoks');

    // Import CSV - all admin levels
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/', [ImportController::class, 'import'])->name('store');
        Route::get('/template', [ImportController::class, 'downloadTemplate'])->name('template');
    });

    // Export CSV - all admin levels (scoped data)
    Route::get('/export/jamaah', [ImportController::class, 'export'])->name('export.jamaah');
}); // End of auth middleware group
