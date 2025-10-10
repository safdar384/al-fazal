<?php

// routes/web.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NewCustomerController;
use App\Http\Controllers\CustomerAreaController;
use App\Http\Controllers\newProductController;
use App\Http\Controllers\companyController;
use App\Http\Controllers\ColourController;
use App\Http\Controllers\CatagoryController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\NewAjentController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\RecoveryVoucherController;
use App\Http\Controllers\customerLedgerController;


// Dashboard
Route::view('/dashboard', 'dashboard');

// Ajent Account Pages
Route::view('/AjentAccountTransection', 'pages.ajentAccount.ajentAccountTransection');
Route::view('/EditAjentAccountTransection', 'pages.ajentAccount.editajentAcTransection');
Route::view('/AjentLedgerView', 'pages.ajentAccount.ajentLedgerView');


// Sale Pages
Route::view('/saleInvoice', 'pages.sale.saleInvoice');

// New Customer Resource Routes
Route::get('/Customer', [NewCustomerController::class, 'index'])->name('Customer.index');
route::get('/Customer/create', [NewCustomerController::class, 'create'])->name('Customer.create');
Route::post('/Customer/store', [NewCustomerController::class, 'store'])->name('Customer.store');
Route::delete('/Customer/{id}', [NewCustomerController::class, 'destroy'])->name('Customer.destroy');
Route::get('/Customer/{id}', [NewCustomerController::class, 'edit'])->name('Customer.edit');
Route::put('/Customer/{id}', [NewCustomerController::class, 'update'])->name('Customer.update');

//new customer area
Route::post('/areas', [CustomerAreaController::class, 'store'])->name('area.store');
Route::get('/areas/list', [CustomerAreaController::class, 'getsAreas'])->name('areas.list');
Route::delete('/areas/{id}', [CustomerAreaController::class, 'destroy'])->name('areas.destroy');

// Product Routes
Route::get('/product', [newProductController::class, 'index'])->name('product.index');        // List products (product.blade)
Route::get('/product/create', [newProductController::class, 'create'])->name('product.create'); // Show create form (newProduct.blade)
Route::post('/product', [newProductController::class, 'store'])->name('product.store');       // Save new product
Route::get('/product/{id}/edit', [newProductController::class, 'edit'])->name('product.edit'); // Show edit form (editProduct.blade)
Route::post('/product/{id}', [newProductController::class, 'update'])->name('product.update'); // Save edit
Route::delete('/product/{id}', [newProductController::class, 'destroy'])->name('product.destroy'); // destroy 

// Company routes
Route::post('/company/create', [companyController::class, 'store'])->name('company.store');
Route::get('/company/list', [companyController::class, 'index'])->name('company.index');
Route::delete('/company/{id}', [companyController::class, 'destroy'])->name('company.destroy');

// Category routes
Route::post('/catagory/create', [CatagoryController::class, 'store'])->name('catagory.store');
Route::get('/catagory/list', [CatagoryController::class, 'index'])->name('catagory.index');
Route::delete('/catagory/{id}', [CatagoryController::class, 'destroy'])->name('catagory.destroy');

// Packing routes
Route::post('/packing/create', [PackingController::class, 'store'])->name('packing.store');
Route::get('/packing/list', [PackingController::class, 'index'])->name('packing.index');
Route::delete('/packing/delete/{id}', [PackingController::class, 'destroy'])->name('packing.destroy');

// Colour routes
Route::post('/coloure/create', [ColourController::class, 'store'])->name('coloure.store');
Route::get('/coloure/list', [colourController::class, 'index'])->name('coloure.index');
Route::delete('/coloure/{id}', [ColourController::class, 'destroy'])->name('coloure.destroy');

// New Ajent Resource Routes
Route::get('/NewAjent', [NewAjentController::class, 'index'])->name('NewAjent.index');
route::get('/NewAjent/create', [NewAjentController::class, 'create'])->name('NewAjent.create');
Route::post('/NewAjent/store', [NewAjentController::class, 'store'])->name('NewAjent.store');
Route::delete('/NewAjent/{id}', [NewAjentController::class, 'destroy'])->name('NewAjent.destroy');
Route::get('/NewAjent/{id}', [NewAjentController::class, 'edit'])->name('NewAjent.edit');
Route::put('/NewAjent/{id}', [NewAjentController::class, 'update'])->name('NewAjent.update');   

// Bank Account Resource Routes
Route::get('/bankAccount', [BankAccountController::class, 'index'])->name('bankAccount.index');
route::get('/bankAccount/create', [BankAccountController::class, 'create'])->name('bankAccount.create');
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



