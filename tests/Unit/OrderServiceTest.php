<?php

namespace Tests\Unit;

use App\Support\SaleInvoiceCalculator;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    public function test_order_totals_match_sale_calculator(): void
    {
        $items = [
            ['productCode' => 'P1', 'qty' => 2, 'rate' => 100, 'discount' => 10],
        ];
        $computed = SaleInvoiceCalculator::compute($items, 5);

        $this->assertEquals(180.0, $computed['invoiceTotal']);
        $this->assertEquals(175.0, $computed['grandTotal']);
    }
}
