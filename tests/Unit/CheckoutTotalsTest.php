<?php

namespace Tests\Unit;

use App\Services\CurrencyService;
use App\Services\ShopifyService;
use App\Support\CheckoutTotals;
use Mockery;
use Tests\TestCase;

class CheckoutTotalsTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_fr_locale_displays_eur_square_charges_usd(): void
    {
        $currency = new CurrencyService;
        $shopify  = Mockery::mock(ShopifyService::class);
        $shopify->shouldReceive('estimateShippingUsd')->andReturn(0.0);

        $totals = new CheckoutTotals($currency, $shopify);

        $result = $totals->calculate([
            ['price' => 100.00, 'quantity' => 1],
        ], 'standard', 'fr');

        $this->assertSame('EUR', $result['currency_code']);
        $this->assertSame('€', $result['currency_symbol']);
        $this->assertSame('USD', $result['square_currency_code']);
        $this->assertSame(10000, $result['square_amount_cents']);
        $this->assertSame(100.0, $result['total_usd']);
        $this->assertGreaterThan(90, $result['subtotal']);
        $this->assertLessThan(95, $result['subtotal']);
        $this->assertNotSame($result['amount_cents'], $result['square_amount_cents']);
    }

    public function test_calculate_includes_tax_in_total(): void
    {
        $currency = new CurrencyService;
        $shopify  = Mockery::mock(ShopifyService::class);
        $shopify->shouldReceive('estimateShippingUsd')->andReturn(9.99);

        $totals = new CheckoutTotals($currency, $shopify);

        $result = $totals->calculate([
            ['price' => 100.00, 'quantity' => 1],
        ], 'standard', 'en', 0, 8.88);

        $this->assertSame(8.88, $result['tax_usd']);
        $this->assertSame(118.87, $result['total_usd']);
    }
}
