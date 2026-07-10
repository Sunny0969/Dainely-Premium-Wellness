<?php

namespace Tests\Unit;

use App\Services\ShopifyService;
use ReflectionMethod;
use Tests\TestCase;

class ShopifyOrderPresentationTest extends TestCase
{
    public function test_fr_checkout_uses_eur_amounts_for_shopify_receipt(): void
    {
        $service = new ShopifyService;
        $method  = new ReflectionMethod(ShopifyService::class, 'resolveCustomerPresentation');
        $method->setAccessible(true);

        $result = $method->invoke($service, [
            'charge_currency'   => 'USD',
            'display_currency'  => 'EUR',
            'display_subtotal'  => 55.15,
            'display_shipping'  => 9.19,
            'display_tax'       => 11.03,
            'display_total'     => 75.37,
            'display_discount'  => 0,
            'exchange_rate'     => 0.92,
        ], 59.95, 9.99, 11.99, 81.93, 0.0);

        $this->assertSame('EUR', $result['currency']);
        $this->assertSame(55.15, $result['subtotal']);
        $this->assertSame(9.19, $result['shipping']);
        $this->assertSame(11.03, $result['tax']);
        $this->assertSame(75.37, $result['total']);
        $this->assertSame(81.93, $result['total_usd']);
    }

    public function test_en_checkout_keeps_usd_presentation(): void
    {
        $service = new ShopifyService;
        $method  = new ReflectionMethod(ShopifyService::class, 'resolveCustomerPresentation');
        $method->setAccessible(true);

        $result = $method->invoke($service, [
            'charge_currency'  => 'USD',
            'display_currency' => 'USD',
            'display_subtotal' => 59.95,
            'display_total'    => 81.93,
            'exchange_rate'    => 1,
        ], 59.95, 9.99, 11.99, 81.93, 0.0);

        $this->assertSame('USD', $result['currency']);
        $this->assertSame(59.95, $result['subtotal']);
        $this->assertSame(81.93, $result['total']);
    }
}
