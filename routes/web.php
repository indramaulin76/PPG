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

    // Jamaah bulk delete (Super Admin only)
    Route::middleware(['role:super_admin'])->group(function () {
        Route::delete('jamaah/destroy-all', [JamaahController::class, 'destroyAll'])->name('jamaah.destroy-all');
    });

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

        // Kelompok - Super Admin can manage (full), Admin Desa can only view & edit
        Route::middleware(['auth'])->group(function () {
            Route::get('/kelompok', [WilayahController::class, 'kelompokIndex'])->name('kelompok.index');
            Route::put('/kelompok/{kelompok}', [WilayahController::class, 'kelompokUpdate'])->name('kelompok.update');
        });

        Route::middleware(['role:super_admin'])->group(function () {
            Route::post('/kelompok', [WilayahController::class, 'kelompokStore'])->name('kelompok.store');
            Route::delete('/kelompok/{kelompok}', [WilayahController::class, 'kelompokDestroy'])->name('kelompok.destroy');
        });
    });

    // Admin Management - Super Admin and Admin Desa (authorization handled in controller)
    Route::middleware(['auth'])->group(function () {
        Route::get('/admin', [AdminManagementController::class, 'index'])->name('admin.index');
        Route::put('/admin/{admin}', [AdminManagementController::class, 'update'])->name('admin.update');
    });

    Route::middleware(['role:super_admin'])->group(function () {
        Route::post('/admin', [AdminManagementController::class, 'store'])->name('admin.store');
        Route::delete('/admin/{admin}', [AdminManagementController::class, 'destroy'])->name('admin.destroy');
    });

    // Web Settings - Super Admin only
    Route::middleware(['role:super_admin'])->group(function () {
        Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    });

    // API: Get kelompoks by desa (for dynamic dropdown)
    Route::get('/api/desa/{desa}/kelompoks', [WilayahController::class, 'getKelompoksByDesa'])->name('api.kelompoks');

    // Import CSV - all admin levels
    Route::prefix('import')->name('import.')->group(function () {
        Route::get('/', [ImportController::class, 'index'])->name('index');
        Route::post('/', [ImportController::class, 'import'])->name('store');
        Route::get('/template', [ImportController::class, 'downloadTemplate'])->name('template');
        Route::get('/template/excel', [ImportController::class, 'downloadTemplateExcel'])->name('template.excel');
    });

    // Export Jamaah - all admin levels (scoped data)
    Route::get('/export/jamaah', [ImportController::class, 'export'])->name('export.jamaah');
    Route::get('/export/jamaah/excel', [ImportController::class, 'exportExcel'])->name('export.jamaah.excel');
}); // End of auth middleware group
