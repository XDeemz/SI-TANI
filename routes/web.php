<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Farmer\CommodityController as FarmerCommodityController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Profil Pengguna (F-02)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Modul Petani: Manajemen Komoditas (F-04)
    Route::prefix('farmer')->name('farmer.')->group(function () {
        Route::patch('commodities/{commodity}/toggle-status', [FarmerCommodityController::class, 'toggleStatus'])
            ->name('commodities.toggle-status');
        Route::resource('commodities', FarmerCommodityController::class);
    });
});

require __DIR__.'/auth.php';
