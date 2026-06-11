<?php

use App\Http\Controllers\Api\RegionController;
use Illuminate\Support\Facades\Route;

Route::prefix('wilayah')->name('api.regions.')->group(function () {
    Route::get('/provinces', [RegionController::class, 'provinces'])->name('provinces');
    Route::get('/regencies', [RegionController::class, 'regencies'])->name('regencies');
    Route::get('/districts', [RegionController::class, 'districts'])->name('districts');
    Route::get('/villages', [RegionController::class, 'villages'])->name('villages');
});
