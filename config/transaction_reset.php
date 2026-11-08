<?php

/**
 * Transaction cleanup (db:reset-transactions).
 *
 * Master data (customers, products, areas, categories, etc.) is NEVER listed here.
 * Tables are truncated in this order (children before parents).
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Allow reset from CLI
    |--------------------------------------------------------------------------
    |
    | Set TRANSACTION_RESET_ENABLED=true in .env when you intend to run
    | php artisan db:reset-transactions (recommended for production).
    |
    */
    'enabled' => env('TRANSACTION_RESET_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Tables that must NEVER be truncated (safety denylist)
    |--------------------------------------------------------------------------
    */
    'protected_tables' => [
        'newcustomers',
        'newCustomers',
        'product_info',
        'customerareas',
        'users',
        'product_catagory',
        'packing',
        'colour',
        'product_company',
        'bank_accounts',
        'labours',
        'units',
        'accounts',
        'password_reset_tokens',
        'sessions',
        'migrations',
        'jobs',
        'job_batches',
        'failed_jobs',
        'cache',
        'cache_locks',
        'client_url_restrictions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Transactional tables (truncate order: dependent rows first)
    |--------------------------------------------------------------------------
    */
    'transactional_tables' => [
        'sale_invoice_bank_payments',
        'sale_invoice_items',
        'sale_invoices',
        'purchase_invoice_items',
        'purchase_invoices',
        'purchase_return_items',
        'purchase_returns',
        'sale_return_items',
        'sale_returns',
        'supplier_transaction_items',
        'supplier_transactions',
        'order_items',
        'orders',
        'recovery_bank_distributions',
        'labour_account_transactions',
        'customerledgers',
        'expense_ledger',
        'expenses',
        'guds',
        'profit_loss_closings',
        'customer_product_rates',
        'product_cost_versions',
        'bom_overheads',
        'bom_labors',
        'bom_items',
        'boms',
        'voucher_group_sequences',
        'ledgers',
        'market_vouchers',
        'login_activities',
    ],

    /*
    |--------------------------------------------------------------------------
    | Optional cache / temp tables
    |--------------------------------------------------------------------------
    */
    'cache_tables' => [
        'cache',
        'cache_locks',
    ],

    /*
    |--------------------------------------------------------------------------
    | After truncate: zero derived balances on master rows (no deletes)
    |--------------------------------------------------------------------------
    */
    'post_reset_updates' => [
        'newCustomers' => ['custBalance' => 0],
        'product_variants' => ['stockQty' => 0],
        'accounts' => ['opening_balance' => 0],
    ],

    /** Confirmation phrase (must be typed exactly when not using --force). */
    'confirmation_phrase' => 'RESET TRANSACTIONS',

];
