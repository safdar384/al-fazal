<?php

return [
    'list_per_page' => (int) env('ORDER_LIST_PER_PAGE', 50),
    'list_max_per_page' => 100,
    /** Optional read-only stock availability check on save (never deducts). */
    'stock_check_on_save' => (bool) env('ORDER_STOCK_CHECK_ON_SAVE', false),
    'statuses' => [
        'Pending',
        'Confirmed',
        'In Process',
        'Completed',
        'Cancelled',
    ],
];
