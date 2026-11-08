<?php

namespace Tests\Unit;

use App\Services\PurchaseInvoiceService;
use App\Support\SaleInvoiceCalculator;
use PHPUnit\Framework\TestCase;

class PurchaseInvoiceServiceTest extends TestCase
{
    public function test_payment_status_resolution(): void
    {
        $service = new PurchaseInvoiceService;
        $method = new \ReflectionMethod($service, 'resolvePaymentStatus');
        $method->setAccessible(true);

        $this->assertEquals('unpaid', $method->invoke($service, 0, 1000));
        $this->assertEquals('partial', $method->invoke($service, 400, 1000));
        $this->assertEquals('paid', $method->invoke($service, 1000, 1000));
    }

    public function test_totals_with_discount(): void
    {
        $computed = SaleInvoiceCalculator::compute([
            ['qty' => 10, 'rate' => 100, 'discount' => 0],
        ], 50);

        $this->assertEquals(1000.0, $computed['invoiceTotal']);
        $this->assertEquals(950.0, $computed['grandTotal']);
    }
}
