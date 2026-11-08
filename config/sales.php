<?php

return [
    'list_per_page' => (int) env('SALE_LIST_PER_PAGE', 50),
    'list_max_per_page' => 100,
    'inventory_enabled' => (bool) env('SALE_INVENTORY_ENABLED', true),
    'cash_customer_code' => env('SALE_CASH_CUSTOMER_CODE', 'CASH'),
    'summary_report_per_page' => (int) env('SALE_SUMMARY_REPORT_PER_PAGE', 50),
    'summary_report_cache_seconds' => (int) env('SALE_SUMMARY_REPORT_CACHE_SECONDS', 300),
];
