<?php

use App\Http\Controllers\AksesorisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DompetPulsaController;
use App\Http\Controllers\PengeluaranController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dompet Pulsa
    Route::resource('dompet-pulsa', DompetPulsaController::class)->parameters([
        'dompet_pulsa' => 'dompetPulsa',
    ]);
    Route::get('dompet-pulsa/{dompetPulsa}/transaksi/create', [DompetPulsaController::class, 'createTransaction'])->name('dompet-pulsa.transaksi.create');
    Route::post('dompet-pulsa/{dompetPulsa}/transaksi', [DompetPulsaController::class, 'storeTransaction'])->name('dompet-pulsa.transaksi.store');
    Route::get('dompet-pulsa/{dompetPulsa}/transaksi/{transaksi}/edit', [DompetPulsaController::class, 'editTransaction'])->name('dompet-pulsa.transaksi.edit');
    Route::put('dompet-pulsa/{dompetPulsa}/transaksi/{transaksi}', [DompetPulsaController::class, 'updateTransaction'])->name('dompet-pulsa.transaksi.update');
    Route::delete('dompet-pulsa/{dompetPulsa}/transaksi/{transaksi}', [DompetPulsaController::class, 'destroyTransaction'])->name('dompet-pulsa.transaksi.destroy');

    // Voucher
    Route::resource('voucher', VoucherController::class);
    Route::get('voucher/{voucher}/transaksi/create', [VoucherController::class, 'createTransaction'])->name('voucher.transaksi.create');
    Route::post('voucher/{voucher}/transaksi', [VoucherController::class, 'storeTransaction'])->name('voucher.transaksi.store');

    // Aksesoris
    Route::resource('aksesoris', AksesorisController::class)->parameters([
        'aksesoris' => 'aksesoris',
    ]);
    Route::get('aksesoris/{aksesoris}/transaksi/create', [AksesorisController::class, 'createTransaction'])->name('aksesoris.transaksi.create');
    Route::post('aksesoris/{aksesoris}/transaksi', [AksesorisController::class, 'storeTransaction'])->name('aksesoris.transaksi.store');

    // Pengeluaran
    Route::resource('pengeluaran', PengeluaranController::class);

    // Reports
    Route::prefix('laporan')->name('laporan.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/penjualan', [ReportController::class, 'penjualan'])->name('penjualan');
        Route::get('/stok', [ReportController::class, 'stok'])->name('stok');
    });
});

require __DIR__.'/auth.php';
