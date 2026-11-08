<?php

return [
    'list_per_page' => (int) env('PURCHASE_LIST_PER_PAGE', 50),
    'list_max_per_page' => 100,
    'inventory_enabled' => (bool) env('PURCHASE_INVENTORY_ENABLED', true),
    'summary_report_cache_seconds' => (int) env('PURCHASE_SUMMARY_CACHE_SECONDS', 300),
];
