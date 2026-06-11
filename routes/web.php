<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\RegionLogoController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PublicReportController;
use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureSuperAdmin;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicReportController::class, 'home'])->name('home');
Route::get('/laporan/buat', [PublicReportController::class, 'create'])->name('reports.create');
Route::post('/laporan', [PublicReportController::class, 'store'])->name('reports.store');
Route::get('/laporan/sukses', [PublicReportController::class, 'success'])->name('reports.success');
Route::get('/cek-status', [PublicReportController::class, 'lookup'])->name('reports.lookup');
Route::get('/laporan/{ticketCode}', [PublicReportController::class, 'show'])
    ->where('ticketCode', 'LM-(?:[0-9]{4}|DEMO)-[0-9]{6}')
    ->name('reports.show');
Route::get('/laporan/{ticketCode}/pdf', [PublicReportController::class, 'pdf'])
    ->where('ticketCode', 'LM-(?:[0-9]{4}|DEMO)-[0-9]{6}')
    ->name('reports.pdf');

Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:admin-login')
        ->name('admin.login.store');
});

Route::middleware(['auth', EnsureAdmin::class])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => Redirect::route('admin.dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/laporan/{report}', [AdminReportController::class, 'show'])->name('reports.show');
    Route::patch('/laporan/{report}', [AdminReportController::class, 'update'])->name('reports.update');
    Route::get('/rekap/excel', [ExportController::class, 'excel'])->name('exports.excel');
    Route::get('/rekap/pdf', [ExportController::class, 'pdf'])->name('exports.pdf');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::middleware(EnsureSuperAdmin::class)->group(function () {
        Route::get('/akun', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('/akun/export/csv', [AccountController::class, 'export'])->name('accounts.export');
        Route::patch('/akun/{user}/password', [AccountController::class, 'updatePassword'])->name('accounts.password.update');
        Route::get('/logo-wilayah', [RegionLogoController::class, 'index'])->name('logos.index');
        Route::post('/logo-wilayah/import-zip', [RegionLogoController::class, 'import'])->name('logos.import');
        Route::post('/logo-wilayah', [RegionLogoController::class, 'store'])->name('logos.store');
        Route::delete('/logo-wilayah/{region}', [RegionLogoController::class, 'destroy'])->where('region', '[0-9.]+')->name('logos.destroy');
    });
});
