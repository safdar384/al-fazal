<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!--begin::Sidebar Brand-->
    <div class="sidebar-brand">
        <a href="{{ url('/dashboard') }}" class="brand-link">
            <i class="bi bi-shop-window fs-4 text-primary"></i>
            <span class="brand-text fw-bold ms-2">TradeGate</span>
        </a>
    </div>
    <!--end::Sidebar Brand-->

    <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2">
            <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="navigation" aria-label="Main navigation" data-accordion="false">
                
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ url('/dashboard') }}" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!--begin::Setup Section-->
                <li class="nav-header">MANAGEMENT</li>

                <!-- Setup -->
                <li class="nav-item {{ request()->is('product*', 'Customer*', 'NewAgent*', 'areas*', 'bankAccount*', 'colours*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('product*', 'Customer*', 'NewAgent*', 'areas*', 'bankAccount*', 'colours*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-gear-fill"></i>
                        <p>
                            Setup
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('product.index') }}" class="nav-link {{ request()->is('product*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-box-seam"></i>
                                <p>Products</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('Customer.index') }}" class="nav-link {{ request()->is('Customer*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-people"></i>
                                <p>Customers</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('areas.index') }}" class="nav-link {{ request()->is('areas*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-geo-alt"></i>
                                <p>Customer Areas</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('NewAgent.index') }}" class="nav-link {{ request()->is('NewAgent*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-person-badge"></i>
                                <p>Agents</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('colours.index') }}" class="nav-link {{ request()->is('colours*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-palette"></i>
                                <p>Colours</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('bankAccount.index') }}" class="nav-link {{ request()->is('bankAccount*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-bank"></i>
                                <p>Bank Accounts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-wallet2"></i>
                                <p>Expense Accounts</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-person-lock"></i>
                                <p>User Login</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!--begin::Transaction Section-->
                <li class="nav-header">TRANSACTIONS</li>

                <!-- Sale -->
                <li class="nav-item {{ request()->is('sale*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('sale*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-bag-check-fill"></i>
                        <p>
                            Sales
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/saleInvoice') }}" class="nav-link {{ request()->is('saleInvoice') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-receipt"></i>
                                <p>Sale Invoice</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-file-text"></i>
                                <p>Invoice Summary</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-percent"></i>
                                <p>Discount Rates</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Purchase -->
                <li class="nav-item {{ request()->is('purchase*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('purchase*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-cart-plus-fill"></i>
                        <p>
                            Purchases
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-receipt"></i>
                                <p>Purchase Invoice</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-file-text"></i>
                                <p>Invoice Summary</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-currency-dollar"></i>
                                <p>Net Rates</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Expense -->
                <li class="nav-item {{ request()->is('expense*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('expense*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-cash-stack"></i>
                        <p>
                            Expenses
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-wallet2"></i>
                                <p>Expense Transaction</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-journal-text"></i>
                                <p>Expense Ledger</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!--begin::Accounts Section-->
                <li class="nav-header">ACCOUNTS</li>

                <!-- Customer Account -->
                <li class="nav-item {{ request()->is('accountTransection', 'ledgerTransection*', 'viewCustomerLedger', 'viewRecoveryReport') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('accountTransection', 'ledgerTransection*', 'viewCustomerLedger', 'viewRecoveryReport') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-lines-fill"></i>
                        <p>
                            Customer Accounts
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/accountTransection') }}" class="nav-link {{ request()->is('accountTransection') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-arrow-left-right"></i>
                                <p>Account Transaction</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('ledgerTransection.index') }}" class="nav-link {{ request()->is('ledgerTransection*') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-cash-coin"></i>
                                <p>Market Recovery</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/viewCustomerLedger') }}" class="nav-link {{ request()->is('viewCustomerLedger') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-journal-text"></i>
                                <p>Ledger Report</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/viewRecoveryReport') }}" class="nav-link {{ request()->is('viewRecoveryReport') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-file-earmark-bar-graph"></i>
                                <p>Recovery Report</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Agent Account -->
                <li class="nav-item {{ request()->is('AgentAccountTransection', 'AgentLedgerView') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('AgentAccountTransection', 'AgentLedgerView') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-person-workspace"></i>
                        <p>
                            Agent Accounts
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/AgentAccountTransection') }}" class="nav-link {{ request()->is('AgentAccountTransection') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-arrow-left-right"></i>
                                <p>Account Transaction</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('/AgentLedgerView') }}" class="nav-link {{ request()->is('AgentLedgerView') ? 'active' : '' }}">
                                <i class="nav-icon bi bi-journal-text"></i>
                                <p>Ledger Report</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!--begin::Reports Section-->
                <li class="nav-header">REPORTS & STOCK</li>

                <!-- Stock -->
                <li class="nav-item {{ request()->is('stock*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('stock*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-boxes"></i>
                        <p>
                            Stock Management
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-clipboard-data"></i>
                                <p>Stock Report</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-box-arrow-up"></i>
                                <p>Sale Stock</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-box-arrow-in-down"></i>
                                <p>Purchase Stock</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Reports -->
                <li class="nav-item {{ request()->is('reports*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('reports*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-graph-up-arrow"></i>
                        <p>
                            Reports
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-calendar-day"></i>
                                <p>Daily Report</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-wallet"></i>
                                <p>Customer Balance</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="nav-icon bi bi-bar-chart-line"></i>
                                <p>Profit & Loss</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul>
            <!--end::Sidebar Menu-->
        </nav>
    </div>
    <!--end::Sidebar Wrapper-->
</aside>
