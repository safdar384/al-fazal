<?php

/**
 * ERP accounting configuration — single source for posting rules.
 *
 * Party sub-ledger: customerledgers (AR/AP per customer/supplier code)
 * General ledger: accounts + ledgers (double-entry control accounts)
 */
return [

    'general_ledger_enabled' => env('ACCOUNTING_GL_ENABLED', true),

    'accounts' => [
        'sales_revenue' => env('ACCOUNT_SALES_REVENUE', 'INC001'),
        'purchases_stock' => env('ACCOUNT_PURCHASES', 'ACC_PUR'),
        'accounts_receivable' => env('ACCOUNT_AR', 'ACC_AR'),
        'accounts_payable' => env('ACCOUNT_AP', 'ACC_AP'),
        'cash_on_hand' => env('ACCOUNT_CASH', 'ACC_CASH'),
    ],

    'categories' => [
        'sale_invoice' => 'Sale Invoice',
        'sale_return' => 'Sale Return',
        'cash_sale' => 'Cash Sale',
        'purchase' => 'Purchase',
        'purchase_return' => 'Purchase Return',
        'recovery' => 'Recovery',
        'discount' => 'Discount',
        'payment' => 'AccountTransaction',
        'transfer' => 'Transfer',
        'bilty' => 'Bilty',
        'transport' => 'Transport',
        // Supplier Transaction module only (hidden from Customer Ledger UI)
        'supplier_purchase' => 'SupplierPurchase',
        'supplier_sale' => 'SupplierSale',
        'supplier_cash' => 'SupplierCash',
    ],

    /*
    |--------------------------------------------------------------------------
    | Party ledger posting rules (customerledgers)
    |--------------------------------------------------------------------------
    | Running balance (signed): Previous + Debit − Credit
    | Customer display (receivable): signed balance
    | Supplier display (payable): −signed balance
    */
    'party_rules' => [
        'customer' => [
            'sale_invoice' => ['debit' => true, 'credit' => false],
            'sale_return' => ['debit' => false, 'credit' => true],
            'payment_received' => ['debit' => false, 'credit' => true],
            'discount' => ['debit' => false, 'credit' => true],
        ],
        'supplier' => [
            'purchase_invoice' => ['debit' => false, 'credit' => true],
            'purchase_return' => ['debit' => true, 'credit' => false],
            'payment_made' => ['debit' => true, 'credit' => false],
        ],
    ],

];
