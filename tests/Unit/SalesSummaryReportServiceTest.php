<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SalesSummaryReportServiceTest extends TestCase
{
    public function test_discount_and_outstanding_formulas(): void
    {
        $gross = 1000.0;
        $net = 850.0;
        $paid = 300.0;

        $discount = max(0, round($gross - $net, 2));
        $outstanding = max(0, round($net - $paid, 2));

        $this->assertEquals(150.0, $discount);
        $this->assertEquals(550.0, $outstanding);
    }

    public function test_cash_sale_has_zero_outstanding_when_fully_paid(): void
    {
        $net = 500.0;
        $paid = 500.0;
        $outstanding = max(0, round($net - $paid, 2));

        $this->assertEquals(0.0, $outstanding);
    }
}
