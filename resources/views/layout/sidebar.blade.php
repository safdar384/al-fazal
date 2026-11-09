<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <div class="sidebar-brand">
        <a href="{{ url('/dashboard') }}" class="brand-link">
            <img src="{{ asset('favicon.png') }}" alt="Al Fazal" class="brand-image opacity-100 shadow-sm" width="32" height="32">
            <span class="brand-text fw-bold ms-2">Al Fazal</span>
        </a>
    </div>

    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false">

                @permission('dashboard')
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi {{ auth()->user()?->isAdmin() ? 'bi-speedometer2' : 'bi-house-door' }}"></i>
                        <p>{{ auth()->user()?->isAdmin() ? 'Dashboard' : 'Home' }}</p>
                    </a>
                </li>
                @endpermission

                @php
                    $u = auth()->user();
                    $hasSales = $u?->hasPermission('sale_invoice') || $u?->hasPermission('sale_return') || $u?->hasPermission('cash_sale') || $u?->hasPermission('sale_summary') || $u?->hasPermission('sales_summary_report') || $u?->hasPermission('bilty_guds_report');
                    $hasOrders = $u?->hasPermission('new_order') || $u?->hasPermission('order_summary') || $u?->hasPermission('order_approval');
                    $hasPurchases = $u?->hasPermission('purchase_invoice') || $u?->hasPermission('purchase_return') || $u?->hasPermission('purchase_summary') || $u?->hasPermission('purchase_reports');
                    $hasStock = $u?->hasPermission('products') || $u?->hasPermission('special_rates') || $u?->hasPermission('product_cost') || $u?->hasPermission('stock_management');
                    $hasBank = $u?->hasPermission('bank_accounts') || $u?->hasPermission('bank_transaction') || $u?->hasPermission('bank_ledger');
                    $hasAccounts = $u?->hasPermission('customers') || $u?->hasPermission('account_transaction') || $u?->hasPermission('market_recovery') || $u?->hasPermission('party_cheques') || $u?->hasPermission('supplier_transaction') || $u?->hasPermission('customer_ledger') || $u?->hasPermission('supplier_ledger');
                    $hasLabour = $u?->hasPermission('labour_setup') || $u?->hasPermission('labour_transaction') || $u?->hasPermission('fitting_labour') || $u?->hasPermission('labour_ledger');
                    $hasExpenses = $u?->hasPermission('expense_transaction') || $u?->hasPermission('expense_ledger');
                    $hasReports = $u?->hasPermission('reports_hub') || $u?->hasPermission('market_voucher');
                @endphp

                @if($hasSales || $hasOrders || $hasPurchases)
                <li class="nav-header">TRANSACTIONS</li>
                @endif

                @if($hasSales || $hasOrders)
                <li class="nav-item {{ request()->is('SaleInvoice*', 'SaleReturn*', 'sale*', 'saleSummary*', 'cash-sale*', 'new-order*', 'order-*', 'reports/bilty-guds*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('SaleInvoice*', 'SaleReturn*', 'sale*', 'saleSummary*', 'cash-sale*', 'new-order*', 'order-*', 'reports/bilty-guds*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-bag-check-fill"></i>
                        <p>Sales <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @permission('sale_invoice')
                        <li class="nav-item">
                            <a href="{{ route('sale.invoice') }}" class="nav-link {{ request()->is('SaleInvoice', 'sale-invoice') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-receipt"></i>
                                <p>Sale Invoice</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('sale_return')
                        <li class="nav-item">
                            <a href="{{ route('sale.return') }}" class="nav-link {{ request()->is('SaleReturn', 'sale-return') || request()->routeIs('sale.return*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-arrow-return-left"></i>
                                <p>Sale Return</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('cash_sale')
                        <li class="nav-item">
                            <a href="{{ url('/cash-sale') }}" class="nav-link {{ request()->is('cash-sale*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-cash-coin"></i>
                                <p>Cash Sale</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('sale_summary')
                        <li class="nav-item">
                            <a href="{{ route('saleSummary') }}" class="nav-link {{ request()->is('saleSummary') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-file-text"></i>
                                <p>Sale Summary</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('sales_summary_report')
                        <li class="nav-item">
                            <a href="{{ route('reports.sales-summary') }}" class="nav-link {{ request()->is('reports/sales-summary*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-clipboard-data"></i>
                                <p>Sales Summary Report</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('bilty_guds_report')
                        <li class="nav-item">
                            <a href="{{ route('reports.bilty-guds') }}" class="nav-link {{ request()->is('reports/bilty-guds*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-truck"></i>
                                <p>Bilty Report by Guds</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('new_order')
                        <li class="nav-item">
                            <a href="{{ route('order.index') }}" class="nav-link {{ request()->is('new-order') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-cart-plus"></i>
                                <p>New Order</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('order_summary')
                        <li class="nav-item">
                            <a href="{{ route('order.summary') }}" class="nav-link {{ request()->is('order-summary*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-journal-check"></i>
                                <p>Order Summary</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('order_approval')
                        <li class="nav-item">
                            <a href="{{ route('order.approval') }}" class="nav-link {{ request()->is('order-approval*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-check2-square"></i>
                                <p>Order Approval</p>
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </li>
                @endif

                @if($hasPurchases)
                <li class="nav-item {{ request()->is('PurchaseInvoice*', 'PurchaseReturn*', 'purchase*', 'purchaseSummary*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('PurchaseInvoice*', 'PurchaseReturn*', 'purchase*', 'purchaseSummary*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-cart-plus-fill"></i>
                        <p>Purchases <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @permission('purchase_invoice')
                        <li class="nav-item">
                            <a href="{{ route('purchase.invoice') }}" class="nav-link {{ request()->is('PurchaseInvoice', 'purchase-invoice') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-receipt"></i>
                                <p>Purchase Invoice</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('purchase_return')
                        <li class="nav-item">
                            <a href="{{ route('purchase.return') }}" class="nav-link {{ request()->is('PurchaseReturn', 'purchase-return') || request()->routeIs('purchase.return*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-arrow-return-right"></i>
                                <p>Purchase Return</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('purchase_summary')
                        <li class="nav-item">
                            <a href="{{ route('purchaseSummary') }}" class="nav-link {{ request()->is('purchaseSummary') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-file-text"></i>
                                <p>Purchase Summary</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('purchase_reports')
                        <li class="nav-item">
                            <a href="{{ route('reports.purchases-summary') }}" class="nav-link {{ request()->is('reports/purchases-summary*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-clipboard-data"></i>
                                <p>Purchase Reports</p>
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </li>
                @endif

                @if($hasStock)
                <li class="nav-header">STOCK MANAGEMENT</li>
                <li class="nav-item {{ request()->is('product*', 'customer-discount*', 'bom*', 'stock-management*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('product*', 'customer-discount*', 'bom*', 'stock-management*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-boxes"></i>
                        <p>Stock Management <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @permission('products')
                        <li class="nav-item">
                            <a href="{{ route('product.index') }}" class="nav-link {{ request()->is('product*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-box-seam"></i>
                                <p>Products</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('special_rates')
                        <li class="nav-item">
                            <a href="{{ route('customerDiscount.index') }}" class="nav-link {{ request()->is('customer-discount*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-percent"></i>
                                <p>Special Rates</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('product_cost')
                        <li class="nav-item">
                            <a href="{{ route('bom.index') }}" class="nav-link {{ request()->is('bom*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-diagram-3"></i>
                                <p>Product Cost</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('stock_management')
                        <li class="nav-item">
                            <a href="{{ route('stock-management.index') }}" class="nav-link {{ request()->is('stock-management*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-clipboard-check"></i>
                                <p>Stock Management</p>
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </li>
                @endif

                @if($hasBank)
                <li class="nav-header">BANK</li>
                <li class="nav-item {{ request()->is('bank-account-transaction*', 'bank-ledger*', 'bank-accounts*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('bank-account-transaction*', 'bank-ledger*', 'bank-accounts*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-bank"></i>
                        <p>Bank <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @permission('bank_accounts')
                        <li class="nav-item">
                            <a href="{{ route('bankAccount.index') }}" class="nav-link {{ request()->is('bank-accounts*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-wallet2"></i>
                                <p>Bank Accounts</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('bank_transaction')
                        <li class="nav-item">
                            <a href="{{ route('bank.account.transaction') }}" class="nav-link {{ request()->is('bank-account-transaction*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-arrow-left-right"></i>
                                <p>Bank Transaction</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('bank_ledger')
                        <li class="nav-item">
                            <a href="{{ route('bank.ledger') }}" class="nav-link {{ request()->is('bank-ledger*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-journal-text"></i>
                                <p>Bank Ledger</p>
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </li>
                @endif

                @if($hasAccounts || $hasLabour || $hasExpenses)
                <li class="nav-header">ACCOUNTS</li>
                @endif

                @if($hasAccounts)
                <li class="nav-item {{ request()->is('account-transaction*', 'customer-ledger*', 'recovery-bank-distribution*', 'supplier-ledger*', 'supplier-transaction*', 'Customer*', 'customers*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('account-transaction*', 'customer-ledger*', 'recovery-bank-distribution*', 'supplier-ledger*', 'supplier-transaction*', 'Customer*', 'customers*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-lines-fill"></i>
                        <p>Party Accounts <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @permission('customers')
                        <li class="nav-item">
                            <a href="{{ route('Customer.index') }}" class="nav-link {{ request()->is('Customer*', 'customers*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-people"></i>
                                <p>Customers / Suppliers</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('account_transaction')
                        <li class="nav-item">
                            <a href="{{ route('accountTransaction.index') }}" class="nav-link {{ request()->is('account-transaction') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-arrow-left-right"></i>
                                <p>Account Transaction</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('market_recovery')
                        <li class="nav-item">
                            <a href="{{ route('recovery.distribution') }}" class="nav-link {{ request()->is('recovery-bank-distribution*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-bank2"></i>
                                <p>Market Recovery</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('party_cheques')
                        <li class="nav-item">
                            <a href="{{ route('partyCheques.index') }}" class="nav-link {{ request()->routeIs('partyCheques.index') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-card-checklist"></i>
                                <p>Party Cheques</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('partyCheques.inProcess') }}" class="nav-link {{ request()->routeIs('partyCheques.inProcess', 'partyCheques.inProcessList') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-hourglass-top"></i>
                                <p>Cheque in Process</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('supplier_transaction')
                        <li class="nav-item">
                            <a href="{{ route('supplierTransaction.index') }}" class="nav-link {{ request()->is('supplier-transaction*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-cart-check"></i>
                                <p>Supplier Transaction</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('customer_ledger')
                        <li class="nav-item">
                            <a href="{{ route('customerLedger.view') }}" class="nav-link {{ request()->is('customer-ledger/view*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-journal-text"></i>
                                <p>Customer Ledger</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('supplier_ledger')
                        <li class="nav-item">
                            <a href="{{ route('supplier.ledger.index') }}" class="nav-link {{ request()->is('supplier-ledger*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-truck"></i>
                                <p>Supplier Ledger</p>
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </li>
                @endif

                @if($hasLabour)
                <li class="nav-item {{ request()->is('labour-account*', 'labour-ledger*', 'fitting-labour*', 'Labour*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('labour-account*', 'labour-ledger*', 'fitting-labour*', 'Labour*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-workspace"></i>
                        <p>Labour <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @permission('labour_setup')
                        <li class="nav-item">
                            <a href="{{ route('Labour.index') }}" class="nav-link {{ request()->is('Labour*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-person-badge"></i>
                                <p>Labour Setup</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('labour_transaction')
                        <li class="nav-item">
                            <a href="{{ route('labour.account.index') }}" class="nav-link {{ request()->is('labour-account*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-arrow-left-right"></i>
                                <p>Account Transaction</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('fitting_labour')
                        <li class="nav-item">
                            <a href="{{ route('fitting-labour.index') }}" class="nav-link {{ request()->is('fitting-labour*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-tools"></i>
                                <p>Fitting Labour</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('labour_ledger')
                        <li class="nav-item">
                            <a href="{{ route('labour.ledger') }}" class="nav-link {{ request()->is('labour-ledger*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-journal-text"></i>
                                <p>Labour Ledger</p>
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </li>
                @endif

                @if($hasExpenses)
                <li class="nav-item {{ request()->is('expense*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('expense*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-cash-stack"></i>
                        <p>Expenses <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @permission('expense_transaction')
                        <li class="nav-item">
                            <a href="{{ route('expense.transactionPage') }}" class="nav-link {{ request()->is('expense/transaction*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-wallet2"></i>
                                <p>Expense Transaction</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('expense_ledger')
                        <li class="nav-item">
                            <a href="{{ route('expense.ledger') }}" class="nav-link {{ request()->is('expense/ledger') || request()->is('expense/ledger/*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-journal-text"></i>
                                <p>Expense Ledger</p>
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </li>
                @endif

                @if($hasReports)
                <li class="nav-header">REPORTS</li>
                <li class="nav-item {{ request()->routeIs('reports.hub', 'reports.market-voucher*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->routeIs('reports.hub', 'reports.market-voucher*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-graph-up-arrow"></i>
                        <p>Reports <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        @permission('reports_hub')
                        <li class="nav-item">
                            <a href="{{ route('reports.hub') }}" class="nav-link {{ request()->routeIs('reports.hub') && !request()->routeIs('reports.market-voucher*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-grid"></i>
                                <p>Reporting Hub</p>
                            </a>
                        </li>
                        @endpermission
                        @permission('market_voucher')
                        <li class="nav-item">
                            <a href="{{ route('reports.market-voucher') }}" class="nav-link {{ request()->routeIs('reports.market-voucher*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-receipt-cutoff"></i>
                                <p>Market Voucher</p>
                            </a>
                        </li>
                        @endpermission
                    </ul>
                </li>
                @endif

                @if(auth()->user()?->isAdmin())
                <li class="nav-header">SETTING</li>
                <li class="nav-item {{ request()->is('admin*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>Setting <i class="nav-arrow bi bi-chevron-right"></i></p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-people"></i>
                                <p>Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.user-access.index') }}" class="nav-link {{ request()->routeIs('admin.user-access.*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-person-lock"></i>
                                <p>User Access</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.password.edit') }}" class="nav-link {{ request()->routeIs('admin.password.*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-key"></i>
                                <p>Change Password</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.client-access.index') }}" class="nav-link {{ request()->routeIs('admin.client-access.*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-shield-lock"></i>
                                <p>Client Access</p>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

            </ul>
        </nav>
    </div>
</aside>
