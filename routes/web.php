<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Farmer\CommodityController as FarmerCommodityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Trader\CommodityCatalogController as TraderCommodityCatalogController;
use App\Http\Controllers\Trader\CommodityOfferController as TraderCommodityOfferController;
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

    // Modul Pedagang: Eksplorasi Komoditas (F-05) & Pengajuan Tawaran (F-06a)
    Route::prefix('trader')->name('trader.')->group(function () {
        // Katalog Komoditas Terbuka (F-05)
        Route::get('commodities', [TraderCommodityCatalogController::class, 'index'])->name('commodities.index');
        Route::get('commodities/{commodity}', [TraderCommodityCatalogController::class, 'show'])->name('commodities.show');

        // Tawaran Pedagang (F-06a)
        Route::post('commodities/{commodity}/offers', [TraderCommodityOfferController::class, 'store'])->name('offers.store');
        Route::get('offers', [TraderCommodityOfferController::class, 'index'])->name('offers.index');
        Route::delete('offers/{offer}', [TraderCommodityOfferController::class, 'destroy'])->name('offers.destroy');
    });
});

require __DIR__.'/auth.php';
