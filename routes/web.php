<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Farmer\CommodityController as FarmerCommodityController;
use App\Http\Controllers\Farmer\CommodityOfferController as FarmerCommodityOfferController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecapItemController;
use App\Http\Controllers\RecapPeriodController;
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

    // Modul Petani: Manajemen Komoditas (F-04) & Peninjauan Tawaran (F-06b)
    Route::prefix('farmer')->name('farmer.')->group(function () {
        Route::patch('commodities/{commodity}/toggle-status', [FarmerCommodityController::class, 'toggleStatus'])
            ->name('commodities.toggle-status');
        Route::resource('commodities', FarmerCommodityController::class);

        Route::get('offers', [FarmerCommodityOfferController::class, 'index'])->name('offers.index');
        Route::patch('offers/{offer}/accept', [FarmerCommodityOfferController::class, 'accept'])->name('offers.accept');
        Route::patch('offers/{offer}/reject', [FarmerCommodityOfferController::class, 'reject'])->name('offers.reject');
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

    // Modul Rekapitulasi: Tab Rekap Terpisah per Pasangan (F-07) & Siklus Periode (F-08) & Sheet Harian (F-09)
    Route::get('/rekap', [RecapPeriodController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/{recapPeriod}', [RecapPeriodController::class, 'show'])->name('rekap.show');
    Route::match(['patch', 'post'], '/rekap/{recapPeriod}/start', [RecapPeriodController::class, 'start'])->name('rekap.start');
    Route::match(['patch', 'post'], '/rekap/{recapPeriod}/close', [RecapPeriodController::class, 'close'])->name('rekap.close');
    Route::post('/rekap/{recapPeriod}/items', [RecapItemController::class, 'store'])->name('rekap.items.store');
    Route::match(['put', 'patch'], '/rekap/{recapPeriod}/items/{recapItem}', [RecapItemController::class, 'update'])->name('rekap.items.update');
    Route::delete('/rekap/{recapPeriod}/items/{recapItem}', [RecapItemController::class, 'destroy'])->name('rekap.items.destroy');

    Route::get('/recap-periods', [RecapPeriodController::class, 'index'])->name('recap-periods.index');
    Route::get('/recap-periods/{recapPeriod}', [RecapPeriodController::class, 'show'])->name('recap-periods.show');
    Route::match(['patch', 'post'], '/recap-periods/{recapPeriod}/start', [RecapPeriodController::class, 'start'])->name('recap-periods.start');
    Route::match(['patch', 'post'], '/recap-periods/{recapPeriod}/close', [RecapPeriodController::class, 'close'])->name('recap-periods.close');
    Route::post('/recap-periods/{recapPeriod}/items', [RecapItemController::class, 'store'])->name('recap-periods.items.store');
    Route::match(['put', 'patch'], '/recap-periods/{recapPeriod}/items/{recapItem}', [RecapItemController::class, 'update'])->name('recap-periods.items.update');
    Route::delete('/recap-periods/{recapPeriod}/items/{recapItem}', [RecapItemController::class, 'destroy'])->name('recap-periods.items.destroy');
});

require __DIR__.'/auth.php';
