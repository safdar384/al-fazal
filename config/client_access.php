<?php

/**
 * Client URL access control.
 * Patterns use Laravel Request::is() syntax. Admins are never blocked.
 * Managed under Setting → Client Access.
 */
return [

    /**
     * Flat list used when DB table is empty (fallback) and for seeding.
     *
     * @var list<string>
     */
    'admin_only_patterns' => [
        'products*',
        'customers*',
        'labours*',
        'areas*',
        'bank-accounts*',
        'bank-account-transaction*',
        'bank-ledger*',
        'api/bank-balance*',
        'colours*',
        'customerDiscount*',
        'bom*',
        'cost*',
        'costing*',
        'admin*',
        'category*',
        'company*',
        'packing*',
        'area/store',
    ],

    /**
     * Human-friendly modules for the admin access matrix.
     * Each module maps to one or more URL patterns.
     * "allow" in the UI = restriction inactive (client can open).
     * "deny"  in the UI = restriction active (client blocked).
     *
     * @var array<string, array{
     *     label: string,
     *     description: string,
     *     icon: string,
     *     group: string,
     *     patterns: list<string>
     * }>
     */
    'modules' => [
        'admin_panel' => [
            'label' => 'Admin & Settings',
            'description' => 'Users, passwords, and system settings',
            'icon' => 'bi-gear-wide-connected',
            'group' => 'System',
            'patterns' => ['admin*'],
        ],
        'products' => [
            'label' => 'Products',
            'description' => 'Product list, create and edit items',
            'icon' => 'bi-box-seam',
            'group' => 'Stock',
            'patterns' => ['products*'],
        ],
        'categories' => [
            'label' => 'Categories',
            'description' => 'Product categories setup',
            'icon' => 'bi-tags',
            'group' => 'Stock',
            'patterns' => ['category*'],
        ],
        'colours' => [
            'label' => 'Colours',
            'description' => 'Colour master data',
            'icon' => 'bi-palette',
            'group' => 'Stock',
            'patterns' => ['colours*'],
        ],
        'packing' => [
            'label' => 'Packing',
            'description' => 'Packing types and setup',
            'icon' => 'bi-box2',
            'group' => 'Stock',
            'patterns' => ['packing*'],
        ],
        'bom' => [
            'label' => 'BOM',
            'description' => 'Bill of materials',
            'icon' => 'bi-diagram-3',
            'group' => 'Stock',
            'patterns' => ['bom*'],
        ],
        'costing' => [
            'label' => 'Costing',
            'description' => 'Product cost and costing tools',
            'icon' => 'bi-calculator',
            'group' => 'Stock',
            'patterns' => ['cost*', 'costing*'],
        ],
        'special_rates' => [
            'label' => 'Special Rates',
            'description' => 'Customer-specific product discounts',
            'icon' => 'bi-percent',
            'group' => 'Stock',
            'patterns' => ['customerDiscount*'],
        ],
        'customers' => [
            'label' => 'Customers / Suppliers',
            'description' => 'Party master — create and edit accounts',
            'icon' => 'bi-people',
            'group' => 'Accounts',
            'patterns' => ['customers*'],
        ],
        'areas' => [
            'label' => 'Areas',
            'description' => 'Area / territory setup',
            'icon' => 'bi-geo-alt',
            'group' => 'Accounts',
            'patterns' => ['areas*', 'area/store'],
        ],
        'company' => [
            'label' => 'Company',
            'description' => 'Company profile settings',
            'icon' => 'bi-building',
            'group' => 'Accounts',
            'patterns' => ['company*'],
        ],
        'labours' => [
            'label' => 'Labour Setup',
            'description' => 'Labour masters and rates',
            'icon' => 'bi-person-workspace',
            'group' => 'Labour',
            'patterns' => ['labours*'],
        ],
        'bank_accounts' => [
            'label' => 'Bank Accounts',
            'description' => 'Bank account master list',
            'icon' => 'bi-bank',
            'group' => 'Bank',
            'patterns' => ['bank-accounts*'],
        ],
        'bank_transactions' => [
            'label' => 'Bank Transactions',
            'description' => 'Post bank deposits and withdrawals',
            'icon' => 'bi-arrow-left-right',
            'group' => 'Bank',
            'patterns' => ['bank-account-transaction*'],
        ],
        'bank_ledger' => [
            'label' => 'Bank Ledger',
            'description' => 'Bank statements and ledger views',
            'icon' => 'bi-journal-text',
            'group' => 'Bank',
            'patterns' => ['bank-ledger*', 'api/bank-balance*'],
        ],
    ],
];
