<?php

/**
 * Role defaults + per-page module catalog for User Access.
 * Admin always has full access.
 */
return [

    'idle_timeout_minutes' => (int) env('SESSION_IDLE_TIMEOUT', 0),

    /**
     * Legacy parent keys → child page keys (for old saved ACL / role lists).
     *
     * @var array<string, list<string>>
     */
    'legacy_groups' => [
        'sales' => ['sale_invoice', 'sale_return', 'cash_sale', 'sale_summary', 'sales_summary_report', 'bilty_guds_report'],
        'orders' => ['new_order', 'order_summary', 'order_approval'],
        'purchases' => ['purchase_invoice', 'purchase_return', 'purchase_summary', 'purchase_reports'],
        'stock' => ['products', 'special_rates', 'product_cost', 'stock_management'],
        'bank' => ['bank_accounts', 'bank_transaction', 'bank_ledger'],
        'accounts' => [
            'customers',
            'account_transaction',
            'market_recovery',
            'party_cheques',
            'supplier_transaction',
            'customer_ledger',
            'supplier_ledger',
        ],
        'labour' => ['labour_setup', 'labour_transaction', 'fitting_labour', 'labour_ledger'],
        'expenses' => ['expense_transaction', 'expense_ledger'],
        'reports' => ['reports_hub', 'market_voucher'],
    ],

    /**
     * Default permission slugs per role (granular page keys).
     *
     * @var array<string, list<string>>
     */
    'roles' => [
        'admin' => ['*'],
        'manager' => [
            'dashboard',
            'sale_invoice', 'sale_return', 'cash_sale', 'sale_summary', 'sales_summary_report', 'bilty_guds_report',
            'new_order', 'order_summary', 'order_approval',
            'purchase_invoice', 'purchase_return', 'purchase_summary', 'purchase_reports',
            'products', 'special_rates', 'product_cost', 'stock_management',
            'bank_accounts', 'bank_transaction', 'bank_ledger',
            'customers', 'account_transaction', 'market_recovery', 'party_cheques', 'supplier_transaction', 'customer_ledger', 'supplier_ledger',
            'labour_setup', 'labour_transaction', 'fitting_labour', 'labour_ledger',
            'expense_transaction', 'expense_ledger',
            'reports_hub', 'market_voucher',
        ],
        'staff' => [
            'dashboard',
            'sale_invoice', 'sale_return', 'cash_sale', 'sale_summary', 'bilty_guds_report',
            'new_order', 'order_summary',
        ],
        'client' => [
            'dashboard',
            'sale_invoice', 'sale_return', 'cash_sale', 'sale_summary', 'sales_summary_report', 'bilty_guds_report',
            'new_order', 'order_summary',
            'purchase_invoice', 'purchase_return', 'purchase_summary',
            'customers', 'account_transaction', 'customer_ledger',
            'expense_transaction', 'expense_ledger',
            'reports_hub',
        ],
    ],

    /**
     * Pages shown in Setting → User Access (URL enforcement + sidebar).
     *
     * @var array<string, array{
     *     label: string,
     *     description: string,
     *     icon: string,
     *     group: string,
     *     patterns: list<string>,
     *     route?: string
     * }>
     */
    'modules' => [

        'dashboard' => [
            'label' => 'Home',
            'description' => 'Entry / home page (always on)',
            'icon' => 'bi-house-door',
            'group' => 'Core',
            'patterns' => ['dashboard', 'api/dashboard*', 'api/navbar*'],
            'route' => 'dashboard',
        ],

        // —— Sales ——
        'sale_invoice' => [
            'label' => 'Sale Invoice',
            'description' => 'Create and edit sale invoices',
            'icon' => 'bi-receipt',
            'group' => 'Sales',
            'patterns' => ['SaleInvoice*', 'sale-invoice*', 'sale/invoice*', 'sale/customer*', 'sale/order*', 'sale/guds*', 'sale/invoices*'],
            'route' => 'sale.invoice',
        ],
        'sale_return' => [
            'label' => 'Sale Return',
            'description' => 'Customer sale return (Good / Damage)',
            'icon' => 'bi-arrow-return-left',
            'group' => 'Sales',
            'patterns' => ['SaleReturn*', 'sale-return*', 'sale/return*'],
            'route' => 'sale.return',
        ],
        'cash_sale' => [
            'label' => 'Cash Sale',
            'description' => 'Cash sale entry',
            'icon' => 'bi-cash-coin',
            'group' => 'Sales',
            'patterns' => ['cash-sale*'],
            'route' => null,
            'url' => '/cash-sale',
        ],
        'sale_summary' => [
            'label' => 'Sale Summary',
            'description' => 'List and print sale summary',
            'icon' => 'bi-file-text',
            'group' => 'Sales',
            'patterns' => ['saleSummary*', 'sale/invoices/print*'],
            'route' => 'saleSummary',
        ],
        'sales_summary_report' => [
            'label' => 'Sales Summary Report',
            'description' => 'Sales summary report screen',
            'icon' => 'bi-clipboard-data',
            'group' => 'Sales',
            'patterns' => ['reports/sales-summary*'],
            'route' => 'reports.sales-summary',
        ],
        'bilty_guds_report' => [
            'label' => 'Bilty Report by Guds',
            'description' => 'Sale invoice bilty info grouped by Guds',
            'icon' => 'bi-truck',
            'group' => 'Sales',
            'patterns' => ['reports/bilty-guds', 'reports/bilty-guds/*'],
            'route' => 'reports.bilty-guds',
        ],

        // —— Orders ——
        'new_order' => [
            'label' => 'New Order',
            'description' => 'Create customer orders',
            'icon' => 'bi-cart-plus',
            'group' => 'Orders',
            'patterns' => ['new-order*', 'order/store*', 'order/update*', 'order/delete*', 'order/product*', 'order/customer*'],
            'route' => 'order.index',
        ],
        'order_summary' => [
            'label' => 'Order Summary',
            'description' => 'View and manage order list',
            'icon' => 'bi-journal-check',
            'group' => 'Orders',
            'patterns' => ['order-summary*'],
            'route' => 'order.summary',
        ],
        'order_approval' => [
            'label' => 'Order Approval',
            'description' => 'Approve or reject orders',
            'icon' => 'bi-check2-square',
            'group' => 'Orders',
            'patterns' => ['order-approval*'],
            'route' => 'order.approval',
        ],

        // —— Purchases ——
        'purchase_invoice' => [
            'label' => 'Purchase Invoice',
            'description' => 'Create purchase invoices',
            'icon' => 'bi-receipt',
            'group' => 'Purchases',
            'patterns' => ['PurchaseInvoice*', 'purchase-invoice*', 'purchase/invoice*', 'purchase/supplier*', 'purchase/product*'],
            'route' => 'purchase.invoice',
        ],
        'purchase_return' => [
            'label' => 'Purchase Return',
            'description' => 'Supplier purchase return (Good / Damage)',
            'icon' => 'bi-arrow-return-right',
            'group' => 'Purchases',
            'patterns' => ['PurchaseReturn*', 'purchase-return*', 'purchase/return*'],
            'route' => 'purchase.return',
        ],
        'purchase_summary' => [
            'label' => 'Purchase Summary',
            'description' => 'Purchase list and summary',
            'icon' => 'bi-file-text',
            'group' => 'Purchases',
            'patterns' => ['purchaseSummary*'],
            'route' => 'purchaseSummary',
        ],
        'purchase_reports' => [
            'label' => 'Purchase Reports',
            'description' => 'Purchases summary report',
            'icon' => 'bi-clipboard-data',
            'group' => 'Purchases',
            'patterns' => ['reports/purchases*'],
            'route' => 'reports.purchases-summary',
        ],

        // —— Stock ——
        'products' => [
            'label' => 'Products',
            'description' => 'Product master list',
            'icon' => 'bi-box-seam',
            'group' => 'Stock',
            'patterns' => [
                'product*', 'products*', 'colours*', 'catagory*', 'category*',
                'packing*', 'company*', 'unit-per-pack*',
            ],
            'route' => 'product.index',
        ],
        'special_rates' => [
            'label' => 'Special Rates',
            'description' => 'Customer special product rates',
            'icon' => 'bi-percent',
            'group' => 'Stock',
            'patterns' => ['customer-discount*', 'customerDiscount*'],
            'route' => 'customerDiscount.index',
        ],
        'product_cost' => [
            'label' => 'Product Cost',
            'description' => 'BOM / product costing',
            'icon' => 'bi-diagram-3',
            'group' => 'Stock',
            'patterns' => ['bom*', 'cost*', 'costing*'],
            'route' => 'bom.index',
        ],
        'stock_management' => [
            'label' => 'Stock Management',
            'description' => 'Stock tools and work stocks',
            'icon' => 'bi-clipboard-check',
            'group' => 'Stock',
            'patterns' => ['stock-management*'],
            'route' => 'stock-management.index',
        ],

        // —— Bank ——
        'bank_accounts' => [
            'label' => 'Bank Accounts',
            'description' => 'Bank account master',
            'icon' => 'bi-wallet2',
            'group' => 'Bank',
            'patterns' => ['bank-accounts*'],
            'route' => 'bankAccount.index',
        ],
        'bank_transaction' => [
            'label' => 'Bank Transaction',
            'description' => 'Post bank transactions',
            'icon' => 'bi-arrow-left-right',
            'group' => 'Bank',
            'patterns' => ['bank-account-transaction*'],
            'route' => 'bank.account.transaction',
        ],
        'bank_ledger' => [
            'label' => 'Bank Ledger',
            'description' => 'Bank ledger and balance',
            'icon' => 'bi-journal-text',
            'group' => 'Bank',
            'patterns' => ['bank-ledger*', 'api/bank-balance*'],
            'route' => 'bank.ledger',
        ],

        // —— Party accounts ——
        'customers' => [
            'label' => 'Customers / Suppliers',
            'description' => 'Party master setup',
            'icon' => 'bi-people',
            'group' => 'Party Accounts',
            'patterns' => ['Customer*', 'customers*', 'areas*', 'area/*', 'customer-catagory*'],
            'route' => 'Customer.index',
        ],
        'account_transaction' => [
            'label' => 'Account Transaction',
            'description' => 'Customer account transactions',
            'icon' => 'bi-arrow-left-right',
            'group' => 'Party Accounts',
            'patterns' => ['account-transaction*'],
            'route' => 'accountTransaction.index',
        ],
        'market_recovery' => [
            'label' => 'Market Recovery',
            'description' => 'Recovery bank distribution',
            'icon' => 'bi-bank2',
            'group' => 'Party Accounts',
            'patterns' => ['recovery-bank-distribution*', 'recovery/*'],
            'route' => 'recovery.distribution',
        ],
        'party_cheques' => [
            'label' => 'Party Cheques',
            'description' => 'Party cheque detail, worker assign, and cheque in process',
            'icon' => 'bi-card-checklist',
            'group' => 'Party Accounts',
            'patterns' => ['party-cheques*'],
            'route' => 'partyCheques.index',
        ],
        'supplier_transaction' => [
            'label' => 'Supplier Transaction',
            'description' => 'Supplier payments and txs',
            'icon' => 'bi-cart-check',
            'group' => 'Party Accounts',
            'patterns' => ['supplier-transaction*'],
            'route' => 'supplierTransaction.index',
        ],
        'customer_ledger' => [
            'label' => 'Customer Ledger',
            'description' => 'Customer ledger views',
            'icon' => 'bi-journal-text',
            'group' => 'Party Accounts',
            'patterns' => ['customer-ledger*'],
            'route' => 'customerLedger.view',
        ],
        'supplier_ledger' => [
            'label' => 'Supplier Ledger',
            'description' => 'Supplier ledger views',
            'icon' => 'bi-truck',
            'group' => 'Party Accounts',
            'patterns' => ['supplier-ledger*'],
            'route' => 'supplier.ledger.index',
        ],

        // —— Labour ——
        'labour_setup' => [
            'label' => 'Labour Setup',
            'description' => 'Labour master setup',
            'icon' => 'bi-person-badge',
            'group' => 'Labour',
            'patterns' => ['Labour*', 'labours*'],
            'route' => 'Labour.index',
        ],
        'labour_transaction' => [
            'label' => 'Labour Account Transaction',
            'description' => 'Labour account posting',
            'icon' => 'bi-arrow-left-right',
            'group' => 'Labour',
            'patterns' => ['labour-account*'],
            'route' => 'labour.account.index',
        ],
        'fitting_labour' => [
            'label' => 'Fitting Labour',
            'description' => 'Fitting labour entry',
            'icon' => 'bi-tools',
            'group' => 'Labour',
            'patterns' => ['fitting-labour*'],
            'route' => 'fitting-labour.index',
        ],
        'labour_ledger' => [
            'label' => 'Labour Ledger',
            'description' => 'Labour ledger',
            'icon' => 'bi-journal-text',
            'group' => 'Labour',
            'patterns' => ['labour-ledger*'],
            'route' => 'labour.ledger',
        ],

        // —— Expenses ——
        'expense_transaction' => [
            'label' => 'Expense Transaction',
            'description' => 'Post expenses',
            'icon' => 'bi-wallet2',
            'group' => 'Expenses',
            'patterns' => ['expense/transaction*', 'expense/store*', 'expense/update*', 'expense/delete*'],
            'route' => 'expense.transactionPage',
        ],
        'expense_ledger' => [
            'label' => 'Expense Ledger',
            'description' => 'Expense ledger',
            'icon' => 'bi-journal-text',
            'group' => 'Expenses',
            'patterns' => ['expense/ledger*', 'expense'],
            'route' => 'expense.ledger',
        ],

        // —— Reports ——
        'reports_hub' => [
            'label' => 'Reporting Hub',
            'description' => 'Main reporting hub',
            'icon' => 'bi-grid',
            'group' => 'Reports',
            'patterns' => ['reports', 'reports/hub*', 'reports/portfolio*', 'reporting*'],
            'route' => 'reports.hub',
        ],
        'market_voucher' => [
            'label' => 'Market Voucher',
            'description' => 'Market voucher report',
            'icon' => 'bi-receipt-cutoff',
            'group' => 'Reports',
            'patterns' => ['reports/market-voucher*'],
            'route' => 'reports.market-voucher',
        ],
    ],
];
