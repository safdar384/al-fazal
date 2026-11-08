<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\SupplierLedgerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', fn () => redirect()->route('dashboard'))->name('home');
    Route::get('/api/dashboard/charts', [HomeController::class, 'chartData'])->name('dashboard.charts');
    Route::get('/api/dashboard/recent-activity', [HomeController::class, 'recentActivityData'])->name('dashboard.recent-activity');

    Route::get('/supplier-ledger', [SupplierLedgerController::class, 'index'])->name('supplier.ledger.index');
    Route::get('/supplier-ledger/balance/{supplierCode}', [SupplierLedgerController::class, 'partyBalance'])->name('supplier.ledger.balance');
    Route::get('/supplier-ledger/generate', [SupplierLedgerController::class, 'generate'])->name('supplier.ledger.generate');
    Route::get('/supplier-ledger/preview/{supplierCode}', [SupplierLedgerController::class, 'preview'])->name('supplier.ledger.preview');
    Route::get('/api/party-balance/{partyCode}', [SupplierLedgerController::class, 'partyBalance'])->name('api.party.balance');
});
