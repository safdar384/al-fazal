<?php

use App\Http\Controllers\Admin\ClientAccessController;
use App\Http\Controllers\Admin\UserAccessController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\BiltyGudsReportController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\BankAccountTransactionController;
use App\Http\Controllers\BOMController;
use App\Http\Controllers\CashSaleController;
use App\Http\Controllers\CatagoryController;
use App\Http\Controllers\ColourController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CostingController;
use App\Http\Controllers\CustomerAreaController;
use App\Http\Controllers\CustomerCatagoryController;
use App\Http\Controllers\CustomerDiscountController;
use App\Http\Controllers\CustomerLedgerController;
use App\Http\Controllers\CustomerReportController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseReportController;
use App\Http\Controllers\FittingLabourController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LabourAccountController;
use App\Http\Controllers\LabourController;
use App\Http\Controllers\LabourReportController;
use App\Http\Controllers\MarketVoucherReportController;
use App\Http\Controllers\NewCustomerController;
use App\Http\Controllers\newProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderReportController;
use App\Http\Controllers\PackingController;
use App\Http\Controllers\PackSizeController;
use App\Http\Controllers\ProductReportController;
use App\Http\Controllers\ProfitLossReportController;
use App\Http\Controllers\ProfitReportController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\PurchaseSummaryReportController;
use App\Http\Controllers\CustomerChequeController;
use App\Http\Controllers\RecoveryBankDistributionController;
use App\Http\Controllers\ReportingController;
use App\Http\Controllers\SaleInvoiceController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SaleReportController;
use App\Http\Controllers\SalesSummaryReportController;
use App\Http\Controllers\StockManagementController;
use App\Http\Controllers\SupplierLedgerController;
use App\Http\Controllers\SupplierTransactionController;
use App\Http\Controllers\UnitPerPackController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Dashboard & navbar
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/home', fn () => redirect()->route('dashboard'))->name('home');
    Route::get('/api/dashboard/charts', [HomeController::class, 'chartData'])->name('dashboard.charts');
    Route::get('/api/dashboard/recent-activity', [HomeController::class, 'recentActivityData'])->name('dashboard.recent-activity');
    Route::get('/api/navbar/search', [HomeController::class, 'navbarSearch'])->name('navbar.search');
    Route::get('/api/navbar/notifications', [HomeController::class, 'navbarNotifications'])->name('navbar.notifications');

    // Bank accounts
    Route::get('/bank-accounts', [BankAccountController::class, 'index'])->name('bankAccount.index');
    Route::get('/bank-accounts/list', [BankAccountController::class, 'listAccounts'])->name('bankAccount.list');
    Route::post('/bank-accounts', [BankAccountController::class, 'store'])->name('bankAccount.store');
    Route::put('/bank-accounts/{id}', [BankAccountController::class, 'update'])->name('bankAccount.update');
    Route::delete('/bank-accounts/{id}', [BankAccountController::class, 'destroy'])->name('bankAccount.destroy');

    // Bank transactions & ledger
    Route::get('/bank-account-transaction', [BankAccountTransactionController::class, 'index'])->name('bank.account.transaction');
    Route::get('/bank-account-transaction/list', [BankAccountTransactionController::class, 'listTransactions'])->name('bank.account.transaction.list');
    Route::post('/bank-account-transaction', [BankAccountTransactionController::class, 'store'])->name('bank.account.transaction.store');
    Route::get('/bank-account-transaction/{id}/data', [BankAccountTransactionController::class, 'transactionData'])->name('bank.account.transaction.data');
    Route::put('/bank-account-transaction/{id}', [BankAccountTransactionController::class, 'updateTransaction'])->name('bank.account.transaction.update');
    Route::delete('/bank-account-transaction/{id}', [BankAccountTransactionController::class, 'destroyTransaction'])->name('bank.account.transaction.destroy');
    Route::get('/bank-ledger', [BankAccountTransactionController::class, 'viewBankLedgerPage'])->name('bank.ledger');
    Route::get('/bank-ledger/preview/{bankAccountCode}', [BankAccountTransactionController::class, 'previewBankLedger'])->name('bank.ledger.preview');
    Route::get('/bank-ledger/{bankAccountCode}', [BankAccountTransactionController::class, 'getBankLedger']);
    Route::get('/api/bank-balance/{bankAccountCode}', [BankAccountTransactionController::class, 'bankSummary'])->name('api.bank.balance');

    // Customers
    Route::get('/Customer', [NewCustomerController::class, 'index'])->name('Customer.index');
    Route::get('/customers', [NewCustomerController::class, 'index']);
    Route::get('/Customer/create', [NewCustomerController::class, 'create'])->name('Customer.create');
    Route::post('/Customer/store', [NewCustomerController::class, 'store'])->name('Customer.store');
    Route::get('/Customer/{id}', [NewCustomerController::class, 'edit'])->name('Customer.edit');
    Route::put('/Customer/{id}', [NewCustomerController::class, 'update'])->name('Customer.update');
    Route::delete('/Customer/{id}', [NewCustomerController::class, 'destroy'])->name('Customer.destroy');

    // Areas (AJAX from customer form modal only — no standalone pages)
    Route::post('/areas', [CustomerAreaController::class, 'store'])->name('area.store');
    Route::delete('/areas/{id}', [CustomerAreaController::class, 'destroy'])->name('areas.destroy');

    // Customer categories (AJAX helper for customer form)
    Route::post('/customer-catagory/create', [CustomerCatagoryController::class, 'store'])->name('customerCatagory.store');
    Route::delete('/customer-catagory/{id}', [CustomerCatagoryController::class, 'destroy'])->name('customerCatagory.destroy');

    // Products
    Route::get('/product', [newProductController::class, 'index'])->name('product.index');
    Route::get('/products', [newProductController::class, 'index']);
    Route::get('/product/create', [newProductController::class, 'create'])->name('product.create');
    Route::post('/product', [newProductController::class, 'store'])->name('product.store');
    Route::get('/product/{id}/edit', [newProductController::class, 'edit'])->name('product.edit');
    Route::put('/product/{id}', [newProductController::class, 'update'])->name('product.update');
    Route::delete('/product/{id}', [newProductController::class, 'destroy'])->name('product.destroy');

    // Product setup helpers
    Route::post('/company/store', [CompanyController::class, 'store'])->name('company.store');
    Route::delete('/company/{id}', [CompanyController::class, 'destroy'])->name('company.destroy');
    Route::post('/catagory/create', [CatagoryController::class, 'store'])->name('catagory.store');
    Route::delete('/catagory/{id}', [CatagoryController::class, 'destroy'])->name('catagory.destroy');
    Route::post('/packing/create', [PackingController::class, 'store'])->name('packing.store');
    Route::delete('/packing/{id}', [PackingController::class, 'destroy'])->name('packing.destroy');
    Route::post('/unit-per-pack/create', [UnitPerPackController::class, 'store'])->name('unitPerPack.store');
    Route::delete('/unit-per-pack/{id}', [UnitPerPackController::class, 'destroy'])->name('unitPerPack.destroy');

    // Pack sizes (packing × unit × qty catalog)
    Route::get('/pack-size', [PackSizeController::class, 'index'])->name('packSize.index');
    Route::post('/pack-size', [PackSizeController::class, 'store'])->name('packSize.store');
    Route::delete('/pack-size/{id}', [PackSizeController::class, 'destroy'])->name('packSize.destroy');

    Route::get('/colours', [ColourController::class, 'index'])->name('colours.index');
    Route::get('/colours/create', [ColourController::class, 'create'])->name('colours.create');
    Route::post('/colours', [ColourController::class, 'store'])->name('colours.store');
    Route::get('/colours/{id}/edit', [ColourController::class, 'edit'])->name('colours.edit');
    Route::put('/colours/{id}', [ColourController::class, 'update'])->name('colours.update');
    Route::delete('/colours/{id}', [ColourController::class, 'destroy'])->name('colours.destroy');

    // Sale invoice
    Route::get('/SaleInvoice', [SaleInvoiceController::class, 'index'])->name('sale.invoice');
    Route::get('/sale-invoice', [SaleInvoiceController::class, 'index']);
    Route::get('/saleSummary', [SaleInvoiceController::class, 'saleSummary'])->name('saleSummary');
    Route::get('/sale/invoices', [SaleInvoiceController::class, 'getAllInvoices']);
    Route::get('/sale/invoices/print', [SaleInvoiceController::class, 'printSummary']);
    Route::put('/sale/invoice/{invoiceNo}/bilty-no', [SaleInvoiceController::class, 'updateBiltyNo'])->name('sale.invoice.biltyNo');
    Route::get('/sale/customer/{customerCode}/orders', [SaleInvoiceController::class, 'customerOrders']);
    Route::get('/sale/order/{orderNo}', [SaleInvoiceController::class, 'orderForInvoice']);
    Route::get('/sale/customer/{customerCode}', [SaleInvoiceController::class, 'getCustomerDetails']);
    Route::get('/sale/invoice/product/{productCode}', [SaleInvoiceController::class, 'getProductDetails']);
    Route::post('/sale/invoice/store', [SaleInvoiceController::class, 'store']);
    Route::get('/sale/invoice/{invoiceNo}/preview', [SaleInvoiceController::class, 'preview'])->name('sale.invoice.preview');
    Route::get('/sale/invoice/edit/{invoiceNo}', [SaleInvoiceController::class, 'edit']);
    Route::put('/sale/invoice/update/{invoiceNo}', [SaleInvoiceController::class, 'update']);
    Route::delete('/sale/invoice/delete/{invoiceNo}', [SaleInvoiceController::class, 'deleteInvoice']);
    Route::post('/sale/guds/store', [SaleInvoiceController::class, 'storeGuds']);
    Route::get('/sale/guds', [SaleInvoiceController::class, 'getAllGuds']);

    // Sale Return
    Route::get('/SaleReturn', [SaleReturnController::class, 'index'])->name('sale.return');
    Route::get('/sale-return', [SaleReturnController::class, 'index']);
    Route::get('/sale/return/product/{productCode}', [SaleReturnController::class, 'getProductDetails']);
    Route::post('/sale/return/store', [SaleReturnController::class, 'store'])->name('sale.return.store');
    Route::get('/sale/return/edit/{returnNo}', [SaleReturnController::class, 'edit'])->name('sale.return.edit');
    Route::put('/sale/return/update/{returnNo}', [SaleReturnController::class, 'update'])->name('sale.return.update');
    Route::delete('/sale/return/delete/{returnNo}', [SaleReturnController::class, 'destroy'])->name('sale.return.delete');
    Route::get('/sale/return/{returnNo}/preview', [SaleReturnController::class, 'preview'])->name('sale.return.preview');

    // Cash sale
    Route::get('/cash-sale', [CashSaleController::class, 'index']);
    Route::post('/cash-sale/store', [CashSaleController::class, 'store'])->name('cash.sale.store');
    Route::get('/cash-sale/edit/{invoiceNo}', [CashSaleController::class, 'edit'])->name('cash.sale.edit');
    Route::put('/cash-sale/{invoiceNo}', [CashSaleController::class, 'update'])->name('cash.sale.update');

    // Fitting Labour (production — same UX pattern as Supplier Transaction)
    Route::get('/fitting-labour', [FittingLabourController::class, 'index'])->name('fitting-labour.index');
    Route::get('/fitting-labour/select-context', [FittingLabourController::class, 'selectContext'])->name('fitting-labour.selectContext');
    Route::get('/fitting-labour/work-stocks', [FittingLabourController::class, 'listWorkStocks'])->name('fitting-labour.workStocks');
    Route::get('/fitting-labour/works', [FittingLabourController::class, 'listWorks'])->name('fitting-labour.works');
    Route::post('/fitting-labour/works', [FittingLabourController::class, 'storeWork'])->name('fitting-labour.works.store');
    Route::delete('/fitting-labour/works/{id}', [FittingLabourController::class, 'destroyWork'])->name('fitting-labour.works.destroy');
    Route::post('/fitting-labour/store', [FittingLabourController::class, 'store'])->name('fitting-labour.store');
    Route::put('/fitting-labour/update/{id}', [FittingLabourController::class, 'update'])->name('fitting-labour.update');
    Route::delete('/fitting-labour/delete/{id}', [FittingLabourController::class, 'destroy'])->name('fitting-labour.destroy');

    // Stock Management (product stock + work stock)
    Route::get('/stock-management', [StockManagementController::class, 'index'])->name('stock-management.index');
    Route::get('/stock-management/work-stocks', [StockManagementController::class, 'listWorkStocks'])->name('stock-management.workStocks');
    Route::post('/stock-management/work-stocks', [StockManagementController::class, 'saveWorkStock'])->name('stock-management.workStocks.save');
    Route::delete('/stock-management/work-stocks', [StockManagementController::class, 'destroyWorkStock'])->name('stock-management.workStocks.destroy');
    Route::put('/stock-management/product-stock', [StockManagementController::class, 'updateProductStock'])->name('stock-management.productStock.update');

    // Orders
    Route::get('/new-order', [OrderController::class, 'index'])->name('order.index');
    Route::post('/order/store', [OrderController::class, 'store'])->name('order.store');
    Route::get('/order-summary', [OrderController::class, 'summary'])->name('order.summary');
    Route::get('/order-summary/print', [OrderController::class, 'printSummary'])->name('order.summary.print');
    Route::get('/order/{orderNo}/preview', [OrderController::class, 'preview'])->name('order.preview');
    Route::get('/order/{orderNo}/edit', [OrderController::class, 'edit'])->name('order.edit');
    Route::put('/order/{orderNo}', [OrderController::class, 'update'])->name('order.update');
    Route::post('/order/{orderNo}/convert-to-invoice', [OrderController::class, 'convertToInvoice'])->name('order.convert');
    Route::post('/order/{orderNo}/status', [OrderController::class, 'updateStatus'])->name('order.status');
    Route::get('/order-approval', [OrderController::class, 'approvalSection'])->name('order.approval');
    Route::post('/order-approval/apply', [OrderController::class, 'applyApprovalStatus'])->name('order.approval.apply');
    Route::post('/order/{orderNo}/approval', [OrderController::class, 'updateApprovalStatus'])->name('order.approval.status');

    Route::get('/reports/orders/summary', [OrderReportController::class, 'summary'])->name('reports.orders.summary');
    Route::get('/reports/orders/pending', [OrderReportController::class, 'pending'])->name('reports.orders.pending');
    Route::get('/reports/orders/customer-history', [OrderReportController::class, 'customerHistory'])->name('reports.orders.customer-history');

    // Purchase invoice
    Route::get('/PurchaseInvoice', [PurchaseInvoiceController::class, 'index'])->name('purchase.invoice');
    Route::get('/purchaseSummary', [PurchaseInvoiceController::class, 'purchaseSummary'])->name('purchaseSummary');
    Route::get('/purchase/invoices', [PurchaseInvoiceController::class, 'getAllInvoices']);
    Route::get('/purchase/invoices/print', [PurchaseInvoiceController::class, 'printInvoices']);
    Route::get('/purchase/invoice/supplier/{supplierCode}', [PurchaseInvoiceController::class, 'getSupplierDetails']);
    Route::get('/purchase/invoice/supplier-products/{supplierCode}', [PurchaseInvoiceController::class, 'getSupplierProducts']);
    Route::get('/purchase/invoice/product/{productCode}', [PurchaseInvoiceController::class, 'getProductDetails']);
    Route::post('/purchase/invoice/store', [PurchaseInvoiceController::class, 'store']);
    Route::get('/purchase/invoice/{invoiceNo}/preview', [PurchaseInvoiceController::class, 'preview'])->name('purchaseInvoice.preview');
    Route::get('/purchase/invoice/edit/{invoiceNo}', [PurchaseInvoiceController::class, 'edit']);
    Route::put('/purchase/invoice/update/{invoiceNo}', [PurchaseInvoiceController::class, 'update']);
    Route::delete('/purchase/invoice/delete/{invoiceNo}', [PurchaseInvoiceController::class, 'deleteInvoice']);
    Route::post('/purchase/invoice/{invoiceNo}/payment', [PurchaseInvoiceController::class, 'recordPayment'])->name('purchase.invoice.payment');

    // Purchase Return
    Route::get('/PurchaseReturn', [PurchaseReturnController::class, 'index'])->name('purchase.return');
    Route::get('/purchase-return', [PurchaseReturnController::class, 'index']);
    Route::get('/purchase/return/product/{productCode}', [PurchaseReturnController::class, 'getProductDetails']);
    Route::post('/purchase/return/store', [PurchaseReturnController::class, 'store'])->name('purchase.return.store');
    Route::get('/purchase/return/edit/{returnNo}', [PurchaseReturnController::class, 'edit'])->name('purchase.return.edit');
    Route::put('/purchase/return/update/{returnNo}', [PurchaseReturnController::class, 'update'])->name('purchase.return.update');
    Route::delete('/purchase/return/delete/{returnNo}', [PurchaseReturnController::class, 'destroy'])->name('purchase.return.delete');
    Route::get('/purchase/return/{returnNo}/preview', [PurchaseReturnController::class, 'preview'])->name('purchase.return.preview');

    Route::get('/reports/purchases-summary', [PurchaseSummaryReportController::class, 'index'])->name('reports.purchases-summary');

    // Party balance API
    Route::get('/api/party-balance/{partyCode}', [SupplierLedgerController::class, 'partyBalance'])->name('api.party.balance');

    // Supplier ledger
    Route::get('/supplier-ledger', [SupplierLedgerController::class, 'index'])->name('supplier.ledger.index');
    Route::get('/supplier-ledger/balance/{supplierCode}', [SupplierLedgerController::class, 'partyBalance'])->name('supplier.ledger.balance');
    Route::get('/supplier-ledger/generate', [SupplierLedgerController::class, 'generate'])->name('supplier.ledger.generate');
    Route::get('/supplier-ledger/preview/{supplierCode}', [SupplierLedgerController::class, 'preview'])->name('supplier.ledger.preview');

    // Supplier Transaction (Sale / Purchase with net rate)
    Route::get('/supplier-transaction', [SupplierTransactionController::class, 'index'])->name('supplierTransaction.index');
    Route::get('/supplier-transaction/select-context', [SupplierTransactionController::class, 'selectContext'])->name('supplierTransaction.selectContext');
    Route::get('/supplier-transaction/catalog', [SupplierTransactionController::class, 'catalog'])->name('supplierTransaction.catalog');
    Route::get('/supplier-transaction/products', [SupplierTransactionController::class, 'products'])->name('supplierTransaction.products');
    Route::get('/supplier-transaction/product-rate', [SupplierTransactionController::class, 'productRate'])->name('supplierTransaction.productRate');
    Route::get('/supplier-transaction/product-context', [SupplierTransactionController::class, 'productContext'])->name('supplierTransaction.productContext');
    Route::get('/supplier-transaction/product-works', [SupplierTransactionController::class, 'productWorks'])->name('supplierTransaction.productWorks');
    Route::get('/supplier-transaction/entries', [SupplierTransactionController::class, 'entries'])->name('supplierTransaction.entries');
    Route::post('/supplier-transaction/store', [SupplierTransactionController::class, 'store'])->name('supplierTransaction.store');
    Route::put('/supplier-transaction/update/{id}', [SupplierTransactionController::class, 'update'])->name('supplierTransaction.update');
    Route::delete('/supplier-transaction/delete/{id}', [SupplierTransactionController::class, 'destroy'])->name('supplierTransaction.destroy');

    // Customer ledger & account transactions
    Route::get('/account-transaction', [CustomerLedgerController::class, 'showAccountTransaction'])->name('accountTransaction.index');
    Route::get('/account-transaction/list', [CustomerLedgerController::class, 'listAccountTransactions'])->name('accountTransaction.list');
    Route::post('/account-transaction/store', [CustomerLedgerController::class, 'storeAccountTransaction'])->name('accountTransaction.store');
    Route::delete('/ledger-transaction/delete/{id}', [CustomerLedgerController::class, 'deleteLedgerEntry']);
    Route::put('/ledger-transaction/update/{id}', [CustomerLedgerController::class, 'updateLedgerEntry']);
    Route::get('/customer-ledger/view', [CustomerLedgerController::class, 'viewCustomerLedgerPage'])->name('customerLedger.view');
    Route::get('/customer-ledger/{customerCode}', [CustomerLedgerController::class, 'getCustomerLedger']);
    Route::get('/customer-ledger/preview/{customerCode}', [CustomerLedgerController::class, 'previewLedger']);
    Route::get('/customer-ledger/entry/{id}', [CustomerLedgerController::class, 'getLedgerEntry']);
    Route::get('/customer/balance/{customerCode}', [CustomerLedgerController::class, 'getBalance']);

    // Market Recovery
    Route::get('/recovery-bank-distribution', [RecoveryBankDistributionController::class, 'index'])->name('recovery.distribution');
    Route::get('/recovery/list', [RecoveryBankDistributionController::class, 'getRecoveryList'])->name('recovery.list');
    Route::get('/recovery/area-ledger/{areaCode}', [RecoveryBankDistributionController::class, 'getAreaLedgerEntries']);
    Route::get('/recovery/area-balance/{areaCode}', [RecoveryBankDistributionController::class, 'getAreaBalance']);
    Route::get('/recovery/area-ledger-print', [RecoveryBankDistributionController::class, 'printAreaLedger'])->name('recovery.area-ledger.print');
    Route::post('/recovery/cheque', [RecoveryBankDistributionController::class, 'storeCheque'])->name('recovery.cheque.store');
    Route::post('/recovery/ledger/approve', [RecoveryBankDistributionController::class, 'approveLedgerEntries'])->name('recovery.ledger.approve');
    Route::delete('/recovery/{id}', [RecoveryBankDistributionController::class, 'deleteRecovery'])->name('recovery.delete');

    // Party cheques (pending list + clear/bounce via AJAX)
    Route::get('/party-cheques', [CustomerChequeController::class, 'index'])->name('partyCheques.index');
    Route::get('/party-cheques/list', [CustomerChequeController::class, 'list'])->name('partyCheques.list');
    Route::get('/party-cheques/print', [CustomerChequeController::class, 'print'])->name('partyCheques.print');
    Route::post('/party-cheques/assign-worker', [CustomerChequeController::class, 'assignWorker'])->name('partyCheques.assignWorker');
    Route::get('/party-cheques/in-process', [CustomerChequeController::class, 'inProcess'])->name('partyCheques.inProcess');
    Route::get('/party-cheques/in-process/list', [CustomerChequeController::class, 'inProcessList'])->name('partyCheques.inProcessList');
    Route::get('/party-cheques/in-process/print', [CustomerChequeController::class, 'printInProcess'])->name('partyCheques.inProcessPrint');
    Route::post('/party-cheques/{id}/pay', [CustomerChequeController::class, 'pay'])->name('partyCheques.pay');
    Route::post('/party-cheques/{id}/clear', [CustomerChequeController::class, 'clear'])->name('partyCheques.clear');
    Route::post('/party-cheques/{id}/bounce', [CustomerChequeController::class, 'bounce'])->name('partyCheques.bounce');

    // Customer discount (special rates)
    Route::get('/customer-discount', [CustomerDiscountController::class, 'index'])->name('customerDiscount.index');
    Route::get('/customer-discount/customer/{customerCode}', [CustomerDiscountController::class, 'getCustomerInfo']);
    Route::get('/customer-discount/product/{productCode}', [CustomerDiscountController::class, 'getProductInfo']);
    Route::get('/customer-discount/rates/{customerCode}', [CustomerDiscountController::class, 'getCustomerRates']);
    Route::get('/customer-discount/history/{customerCode}/{productCode}', [CustomerDiscountController::class, 'getRateHistory']);
    Route::post('/customer-discount/store', [CustomerDiscountController::class, 'store'])->name('customerDiscount.store');
    Route::delete('/customer-discount/{id}', [CustomerDiscountController::class, 'delete'])->name('customerDiscount.delete');

    // Expenses
    Route::get('/expense/transaction', [ExpenseController::class, 'expenseTransactionPage'])->name('expense.transactionPage');
    Route::get('/expense/ledger', [ExpenseController::class, 'expenseLedgerPage'])->name('expense.ledger');
    Route::get('/expense/ledger/report', [ExpenseController::class, 'getExpenseLedgerReport'])->name('expense.ledger.report');
    Route::get('/expense-report-print', [ExpenseController::class, 'expenseReportPrintPage'])->name('expense.report.print');
    Route::get('/expense/{expenseCode}/details', [ExpenseController::class, 'getExpenseDetails']);
    Route::get('/expense/{expenseCode}/ledger', [ExpenseController::class, 'getExpenseLedger']);
    Route::post('/expense/store', [ExpenseController::class, 'storeExpenseTransaction'])->name('expense.storeTransaction');
    Route::post('/expense/store-expense', [ExpenseController::class, 'storeExpense'])->name('expense.storeExpense');
    Route::delete('/expense/{expenseCode}', [ExpenseController::class, 'deleteExpense']);
    Route::put('/expense/ledger/{id}', [ExpenseController::class, 'updateExpenseLedger']);
    Route::delete('/expense/ledger/{id}', [ExpenseController::class, 'deleteExpenseLedger']);

    // Product Cost
    Route::get('/bom', [BOMController::class, 'index'])->name('bom.index');
    Route::get('/bom/{productCode}/report', [BOMController::class, 'costReport'])->name('bom.cost.report');
    Route::get('/bom/{productCode}', [BOMController::class, 'builder'])->name('bom.builder');
    Route::post('/bom/store', [BOMController::class, 'store'])->name('bom.store');
    Route::post('/bom/material', [BOMController::class, 'storeMaterial'])->name('bom.material.store');
    Route::put('/bom/material/{id}', [BOMController::class, 'updateMaterial'])->name('bom.material.update');
    Route::delete('/bom/material/{id}', [BOMController::class, 'destroyMaterial'])->name('bom.material.destroy');
    Route::post('/bom/labour-work', [BOMController::class, 'storeLabourWork'])->name('bom.labour.work.store');
    Route::put('/bom/labour-work/{id}', [BOMController::class, 'updateLabourWork'])->name('bom.labour.work.update');
    Route::delete('/bom/labour-work/{id}', [BOMController::class, 'destroyLabourWork'])->name('bom.labour.work.destroy');
    Route::post('/costing/calculate', [CostingController::class, 'calculate'])->name('cost.calculate');
    Route::post('/costing/version/save', [CostingController::class, 'saveVersion'])->name('cost.version.save');
    Route::get('/costing/version/history/{productCode}', [CostingController::class, 'history'])->name('cost.version.history');

    // Reporting hub
    Route::get('/reports', [ReportingController::class, 'hub'])->name('reports.hub');
    Route::get('/reports/portfolio', [ReportingController::class, 'hubPortfolio'])->name('reports.hub.portfolio');
    Route::get('/reports/sale', [ReportingController::class, 'sale'])->name('reports.sale');
    Route::get('/reports/purchase', [ReportingController::class, 'purchase'])->name('reports.purchase');
    Route::get('/reports/customer', [ReportingController::class, 'customer'])->name('reports.customer');
    Route::get('/reports/labor', fn () => redirect()->route('reports.labour.summary'))->name('reports.labor');
    Route::get('/reports/expense', fn () => redirect()->route('reports.expense.summary'))->name('reports.expense');
    Route::get('/reports/expense/summary', [ExpenseReportController::class, 'summary'])->name('reports.expense.summary');
    Route::get('/reports/expense/by-category', [ExpenseReportController::class, 'byCategory'])->name('reports.expense.by-category');
    Route::get('/reports/product', [ReportingController::class, 'product'])->name('reports.product');

    // Sale reports
    Route::get('/reports/bilty-guds', [BiltyGudsReportController::class, 'index'])->name('reports.bilty-guds');
    Route::get('/reports/bilty-guds/data', [BiltyGudsReportController::class, 'data'])->name('reports.bilty-guds.data');
    Route::get('/reports/bilty-guds/print', [BiltyGudsReportController::class, 'print'])->name('reports.bilty-guds.print');
    Route::post('/reports/bilty-guds/delivered-bulk', [BiltyGudsReportController::class, 'bulkDelivered'])
        ->name('reports.bilty-guds.delivered-bulk');
    Route::get('/reports/sales-summary', [SalesSummaryReportController::class, 'index'])->name('reports.sales-summary');
    Route::get('/reports/sales/summary', [SaleReportController::class, 'summary'])->name('reports.sales.summary');
    Route::get('/reports/sales/by-company', [SaleReportController::class, 'byCompany'])->name('reports.sales.by-company');
    Route::get('/reports/sales/by-product', [SaleReportController::class, 'byProduct'])->name('reports.sales.by-product');
    Route::get('/reports/sales/by-area', [SaleReportController::class, 'byArea'])->name('reports.sales.by-area');
    Route::get('/reports/sales/by-customer', [SaleReportController::class, 'byCustomer'])->name('reports.sales.by-customer');
    Route::get('/reports/sales/by-customer-product', [SaleReportController::class, 'byCustomerProduct'])->name('reports.sales.by-customer-product');

    // Purchase reports
    Route::get('/reports/purchases/summary', [PurchaseReportController::class, 'summary'])->name('reports.purchases.summary');
    Route::get('/reports/purchases/by-company', [PurchaseReportController::class, 'byCompany'])->name('reports.purchases.by-company');
    Route::get('/reports/purchases/by-product', [PurchaseReportController::class, 'byProduct'])->name('reports.purchases.by-product');
    Route::get('/reports/purchases/by-area', [PurchaseReportController::class, 'byArea'])->name('reports.purchases.by-area');
    Route::get('/reports/purchases/by-supplier', [PurchaseReportController::class, 'bySupplier'])->name('reports.purchases.by-supplier');
    Route::get('/reports/purchases/by-supplier-product', [PurchaseReportController::class, 'bySupplierProduct'])->name('reports.purchases.by-supplier-product');

    // Customer reports
    Route::get('/reports/customers/balance', [CustomerReportController::class, 'balance'])->name('reports.customers.balance');
    Route::get('/reports/customers/balance-area', [CustomerReportController::class, 'balanceByArea'])->name('reports.customers.balance-area');
    Route::get('/reports/customers/balance-name', [CustomerReportController::class, 'balanceByName'])->name('reports.customers.balance-name');

    // Labour reports
    Route::get('/reports/labour/summary', [LabourReportController::class, 'summary'])->name('reports.labour.summary');
    Route::get('/reports/labour/by-worker', [LabourReportController::class, 'byWorker'])->name('reports.labour.by-worker');
    Route::get('/reports/labour/by-ajent', [LabourReportController::class, 'byAjent'])->name('reports.labour.by-ajent');
    Route::get('/reports/labour/by-fitter', [LabourReportController::class, 'byFitter'])->name('reports.labour.by-fitter');
    Route::get('/reports/labour/advance', [LabourReportController::class, 'advance'])->name('reports.labour.advance');

    // Market Voucher report
    Route::get('/reports/market-voucher', [MarketVoucherReportController::class, 'index'])->name('reports.market-voucher');
    Route::get('/reports/market-voucher/data', [MarketVoucherReportController::class, 'data'])->name('reports.market-voucher.data');
    Route::get('/reports/market-voucher/print', [MarketVoucherReportController::class, 'print'])->name('reports.market-voucher.print');

    // Product reports
    Route::get('/reports/products/all', [ProductReportController::class, 'all'])->name('reports.products.all');
    Route::get('/reports/products/by-company', [ProductReportController::class, 'byCompany'])->name('reports.products.by-company');
    Route::get('/reports/products/by-name', [ProductReportController::class, 'byName'])->name('reports.products.by-name');
    Route::get('/reports/products/by-category', [ProductReportController::class, 'byCategory'])->name('reports.products.by-category');
    Route::get('/reports/products/short-stock', [ProductReportController::class, 'shortStock'])->name('reports.products.short-stock');

    // Sale invoice profit reports
    Route::get('/reports/sale-profit', [ProfitReportController::class, 'all'])->name('reports.sale-profit.all');
    Route::get('/reports/sale-profit/by-area', [ProfitReportController::class, 'byArea'])->name('reports.sale-profit.by-area');
    Route::get('/reports/sale-profit/by-customer', [ProfitReportController::class, 'byCustomer'])->name('reports.sale-profit.by-customer');
    Route::get('/reports/sale-profit/by-ajent', [ProfitReportController::class, 'byAjent'])->name('reports.sale-profit.by-ajent');
    // Legacy product-profit URLs → sale invoice profit (all)
    Route::get('/reports/product-profit', [ProfitReportController::class, 'all'])->name('reports.product-profit.index');
    Route::get('/reports/product-profit/generate', [ProfitReportController::class, 'all'])->name('reports.product-profit.generate');

    // Profit & loss reports
    Route::get('/reports/profit-loss/statement', [ProfitLossReportController::class, 'statement'])->name('reports.profit-loss.statement');
    Route::get('/reports/profit-loss/closings', [ProfitLossReportController::class, 'closings'])->name('reports.profit-loss.closings');
    Route::post('/reports/profit-loss/closings', [ProfitLossReportController::class, 'storeClosing'])->name('reports.profit-loss.closings.store');
    Route::get('/reports/profit-loss/closing-history', [ProfitLossReportController::class, 'closingHistoryReport'])->name('reports.profit-loss.closing-history');
    Route::get('/reports/profit-loss/closing-history/{closing}', [ProfitLossReportController::class, 'closingHistoryShow'])->name('reports.profit-loss.closing-history.show');

    // Labour setup (master data)
    Route::get('/Labour', [LabourController::class, 'index'])->name('Labour.index');
    Route::get('/Labour/create', [LabourController::class, 'create'])->name('Labour.create');
    Route::post('/Labour/store', [LabourController::class, 'store'])->name('Labour.store');
    Route::get('/Labour/{id}', [LabourController::class, 'edit'])->name('Labour.edit');
    Route::put('/Labour/{id}', [LabourController::class, 'update'])->name('Labour.update');
    Route::delete('/Labour/{id}', [LabourController::class, 'destroy'])->name('Labour.destroy');

    // Labour account transactions
    Route::get('/labour-account', [LabourAccountController::class, 'index'])->name('labour.account.index');
    Route::post('/labour-account', [LabourAccountController::class, 'store'])->name('labour.account.store');
    Route::get('/labour-account/recent', [LabourAccountController::class, 'listRecent'])->name('labour.account.recent');
    Route::get('/labour-account/labour/{id}', [LabourAccountController::class, 'getLabourDetails'])->name('labour.account.details');
    Route::get('/labour-account/transaction/{transaction}', [LabourAccountController::class, 'transactionData'])->name('labour.account.transaction.data');
    Route::put('/labour-account/transaction/{transaction}', [LabourAccountController::class, 'updateTransaction'])->name('labour.account.transaction.update');
    Route::delete('/labour-account/transaction/{transaction}', [LabourAccountController::class, 'destroyTransaction'])->name('labour.account.transaction.destroy');

    // Labour ledger
    Route::get('/labour-ledger', [LabourAccountController::class, 'viewLabourLedgerPage'])->name('labour.ledger');
    Route::get('/labour-ledger/preview/{labourCode}', [LabourAccountController::class, 'previewLabourLedger'])->name('labour.ledger.preview');
    Route::get('/labour-ledger/{labourCode}', [LabourAccountController::class, 'getLabourLedger']);

    // Admin settings (admin role only)
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}/password', [UserManagementController::class, 'updateUserPassword'])->name('users.password.update');

        Route::get('/password', [UserManagementController::class, 'editPassword'])->name('password.edit');
        Route::put('/password', [UserManagementController::class, 'updateOwnPassword'])->name('password.update');

        Route::get('/user-access', [UserAccessController::class, 'index'])->name('user-access.index');
        Route::get('/user-access/{user}', [UserAccessController::class, 'edit'])->name('user-access.edit');
        Route::put('/user-access/{user}', [UserAccessController::class, 'update'])->name('user-access.update');
        Route::post('/user-access/{user}/reset', [UserAccessController::class, 'reset'])->name('user-access.reset');

        Route::get('/client-access', [ClientAccessController::class, 'index'])->name('client-access.index');
        Route::post('/client-access', [ClientAccessController::class, 'store'])->name('client-access.store');
        Route::post('/client-access/sync-module', [ClientAccessController::class, 'syncModule'])->name('client-access.sync-module');
        Route::post('/client-access/bulk-sync', [ClientAccessController::class, 'bulkSync'])->name('client-access.bulk-sync');
        Route::post('/client-access/seed-defaults', [ClientAccessController::class, 'seedDefaults'])->name('client-access.seed-defaults');
        Route::patch('/client-access/{restriction}', [ClientAccessController::class, 'update'])->name('client-access.update');
        Route::delete('/client-access/{restriction}', [ClientAccessController::class, 'destroy'])->name('client-access.destroy');
    });
});
