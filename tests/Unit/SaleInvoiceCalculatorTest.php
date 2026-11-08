<?php

use App\Support\SaleInvoiceCalculator;

test('line amount applies item discount percent', function () {
    expect(SaleInvoiceCalculator::lineAmount(10, 100, 10))->toBe(900.0);
});

test('compute totals with flat invoice discount', function () {
    $result = SaleInvoiceCalculator::compute([
        ['productCode' => 'P1', 'qty' => 2, 'rate' => 100, 'discount' => 0],
        ['productCode' => 'P2', 'qty' => 1, 'rate' => 50, 'discount' => 10],
    ], 25);

    expect($result['invoiceTotal'])->toBe(245.0);
    expect($result['invoiceDiscount'])->toBe(25.0);
    expect($result['grandTotal'])->toBe(220.0);
    expect($result['noOfItems'])->toBe(2);
});
