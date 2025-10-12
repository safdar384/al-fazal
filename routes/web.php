<?php

// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewCustomerController;
use App\Http\Controllers\CustomerAreaController;
use App\Http\Controllers\newProductController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ColourController;
use App\Http\Controllers\CatagoryController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\NewAgentController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\RecoveryVoucherController;
use App\Http\Controllers\customerLedgerController;

// Root Route - Direct to dashboard (no login required)
Route::get('/', function () {
    return redirect('/dashboard');
});

// Home route (alias for dashboard)
Route::get('/home', function () {
    return redirect('/dashboard');
})->name('home');

// Dashboard
Route::view('/dashboard', 'dashboard')->name('dashboard');

// Agent Account Pages
Route::view('/AgentAccountTransection', 'pages.agentAccount.agentAccountTransection');
Route::view('/EditAgentAccountTransection', 'pages.agentAccount.editAgentAcTransection');
Route::view('/AgentLedgerView', 'pages.agentAccount.agentLedgerView');

// Sale Pages
Route::view('/saleInvoice', 'pages.sale.saleInvoice');

// New Customer Resource Routes
Route::get('/Customer', [NewCustomerController::class, 'index'])->name('Customer.index');
Route::get('/Customer/create', [NewCustomerController::class, 'create'])->name('Customer.create');
Route::post('/Customer/store', [NewCustomerController::class, 'store'])->name('Customer.store');
Route::delete('/Customer/{id}', [NewCustomerController::class, 'destroy'])->name('Customer.destroy');
Route::get('/Customer/{id}', [NewCustomerController::class, 'edit'])->name('Customer.edit');
Route::put('/Customer/{id}', [NewCustomerController::class, 'update'])->name('Customer.update');

// Customer Area CRUD
Route::get('/areas', [CustomerAreaController::class, 'index'])->name('areas.index');
Route::get('/areas/create', [CustomerAreaController::class, 'create'])->name('areas.create');
Route::post('/areas', [CustomerAreaController::class, 'store'])->name('area.store');
Route::get('/areas/{id}/edit', [CustomerAreaController::class, 'edit'])->name('areas.edit');
Route::put('/areas/{id}', [CustomerAreaController::class, 'update'])->name('areas.update');
Route::delete('/areas/{id}', [CustomerAreaController::class, 'destroy'])->name('areas.destroy');
Route::get('/areas/list', [CustomerAreaController::class, 'getsAreas'])->name('areas.list');

// Product Routes
Route::get('/product', [newProductController::class, 'index'])->name('product.index');
Route::get('/product/create', [newProductController::class, 'create'])->name('product.create');
Route::post('/product', [newProductController::class, 'store'])->name('product.store');
Route::get('/product/{id}/edit', [newProductController::class, 'edit'])->name('product.edit');
Route::put('/product/{id}', [newProductController::class, 'update'])->name('product.update');
Route::delete('/product/{id}', [newProductController::class, 'destroy'])->name('product.destroy');

// Company routes (used in product create modal)
Route::post('/company/create', [CompanyController::class, 'store'])->name('company.store');
Route::delete('/company/{id}', [CompanyController::class, 'destroy'])->name('company.destroy');

// Category routes (used in product create modal)
Route::post('/catagory/create', [CatagoryController::class, 'store'])->name('catagory.store');
Route::delete('/catagory/{id}', [CatagoryController::class, 'destroy'])->name('catagory.destroy');

// Packing routes (used in product create modal)
Route::post('/packing/create', [PackingController::class, 'store'])->name('packing.store');
Route::delete('/packing/{id}', [PackingController::class, 'destroy'])->name('packing.destroy');

// Colour routes
Route::get('/colours', [ColourController::class, 'index'])->name('colours.index');
Route::get('/colours/create', [ColourController::class, 'create'])->name('colours.create');
Route::post('/colours', [ColourController::class, 'store'])->name('colours.store');
Route::get('/colours/{id}/edit', [ColourController::class, 'edit'])->name('colours.edit');
Route::put('/colours/{id}', [ColourController::class, 'update'])->name('colours.update');
Route::delete('/colours/{id}', [ColourController::class, 'destroy'])->name('colours.destroy');

// New Agent Resource Routes
Route::get('/NewAgent', [NewAgentController::class, 'index'])->name('NewAgent.index');
Route::get('/NewAgent/create', [NewAgentController::class, 'create'])->name('NewAgent.create');
Route::post('/NewAgent/store', [NewAgentController::class, 'store'])->name('NewAgent.store');
Route::delete('/NewAgent/{id}', [NewAgentController::class, 'destroy'])->name('NewAgent.destroy');
Route::get('/NewAgent/{id}', [NewAgentController::class, 'edit'])->name('NewAgent.edit');
Route::put('/NewAgent/{id}', [NewAgentController::class, 'update'])->name('NewAgent.update');

// Bank Account Resource Routes
Route::get('/bankAccount', [BankAccountController::class, 'index'])->name('bankAccount.index');
Route::get('/bankAccount/create', [BankAccountController::class, 'create'])->name('bankAccount.create');
Route::post('/bankAccount/store', [BankAccountController::class, 'store'])->name('bankAccount.store');
Route::delete('/bankAccount/{id}', [BankAccountController::class, 'destroy'])->name('bankAccount.destroy');
Route::get('/bankAccount/{id}', [BankAccountController::class, 'edit'])->name('bankAccount.edit');
Route::put('/bankAccount/{id}', [BankAccountController::class, 'update'])->name('bankAccount.update');

// Recovery transection Routes
Route::get('/ledgerTransection', [customerLedgerController::class, 'index'])->name('ledgerTransection.index');
Route::get('/ledgerTransection/get-customers/{areaCode}', [customerLedgerController::class, 'getCustomersByArea']);
Route::get('/ledgerTransection/get-vouchers/{areaCode}', [customerLedgerController::class, 'getVouchersByArea']);
Route::post('/ledgerTransection/save', [customerLedgerController::class, 'store'])->name('ledgerTransection.store');
Route::get('/ledgerTransection/balance/{customerCode}', [customerLedgerController::class, 'getBalance']);
Route::get('/ledgerTransection/by-area/{areaCode}', [customerLedgerController::class, 'getByArea']);

Route::delete('/ledgerTransection/delete/{id}', [customerLedgerController::class, 'destroy']);
Route::put('/ledgerTransection/{id}', [customerLedgerController::class, 'update'])->name('ledgerTransection.update');
Route::get('/ledgerTransection/{id}/edit', [customerLedgerController::class, 'edit']);
