<?php

namespace Tests\Unit;

use App\Services\CashSaleService;
use App\Support\SaleInvoiceCalculator;
use PHPUnit\Framework\TestCase;

class CashSaleServiceTest extends TestCase
{
    public function test_bank_payments_must_match_grand_total(): void
    {
        $items = [
            ['productCode' => 'P1', 'qty' => 2, 'rate' => 100, 'discount' => 0],
        ];
        $computed = SaleInvoiceCalculator::compute($items, 10);

        $service = new CashSaleService;
        $method = new \ReflectionMethod($service, 'assertBankPayments');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);
        $method->invoke($service, [
            ['bank_account' => 'B1', 'amount' => 50],
        ], $computed['grandTotal']);
    }

    public function test_bank_payments_accept_when_equal_to_grand_total(): void
    {
        $items = [
            ['productCode' => 'P1', 'qty' => 1, 'rate' => 500, 'discount' => 0],
        ];
        $computed = SaleInvoiceCalculator::compute($items, 0);

        $service = new CashSaleService;
        $method = new \ReflectionMethod($service, 'assertBankPayments');
        $method->setAccessible(true);

        $method->invoke($service, [
            ['bank_account' => 'B1', 'amount' => $computed['grandTotal']],
        ], $computed['grandTotal']);

        $this->assertEquals(500.0, $computed['grandTotal']);
    }
}
