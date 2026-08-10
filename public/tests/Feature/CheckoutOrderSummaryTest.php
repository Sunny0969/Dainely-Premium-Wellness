<?php

namespace Tests\Feature;

use App\Support\CheckoutCart;
use Tests\TestCase;

class CheckoutOrderSummaryTest extends TestCase
{
    protected function tearDown(): void
    {
        CheckoutCart::clear();
        parent::tearDown();
    }

    public function test_checkout_renders_order_summary_with_variant_and_totals(): void
    {
        CheckoutCart::addItem([
            'product_id'   => 'dainely-belt',
            'title'        => 'Dainely Belt',
            'subtitle'     => 'Premium Lumbar Support',
            'image'        => '/images/dainely-belt-product.png',
            'price'        => 89.00,
            'quantity'     => 1,
            'option_label' => '2XL',
            'option_value' => '2xl',
        ]);

        $response = $this->get('/en/checkout');

        $response->assertOk();
        $response->assertSee('id="checkout-order-summary"', false);
        $response->assertSee('Dainely Belt', false);
        $response->assertSee('Size: 2XL', false);
        $response->assertSee(__('checkout.subtotal'), false);
        $response->assertSee(__('checkout.order_summary'), false);
        $response->assertSee('window.__CHECKOUT__', false);
        $response->assertDontSee("__('checkout.size_label')", false);
    }

    public function test_fr_checkout_locks_display_currency_to_eur(): void
    {
        CheckoutCart::addItem([
            'product_id' => 'dainely-belt',
            'title'      => 'Dainely Belt',
            'image'      => '/images/dainely-belt-product.png',
            'price'      => 89.00,
            'quantity'   => 1,
        ]);

        $response = $this->get('/fr/checkout');

        $response->assertOk();
        $response->assertSee('"currencyCode":"EUR"', false);
        $response->assertSee('"paymentCurrency":"EUR"', false);
        $response->assertSee('"squareLocale":"fr-FR"', false);
        $response->assertSee('data-payment-currency="EUR"', false);
        $response->assertSee('"lockDisplayCurrency":true', false);
        $response->assertSee('data-display-currency="EUR"', false);
        $response->assertSee('value="FR" selected', false);
    }

    public function test_za_geo_checkout_uses_zar_and_default_country(): void
    {
        CheckoutCart::addItem([
            'product_id' => 'dainely-belt',
            'title'      => 'Dainely Belt',
            'image'      => '/images/dainely-belt-product.png',
            'price'      => 89.00,
            'quantity'   => 1,
        ]);

        $response = $this->withHeaders(['CF-IPCountry' => 'ZA'])->get('/en/checkout');

        $response->assertOk();
        $response->assertSee('"currencyCode":"ZAR"', false);
        $response->assertSee('"paymentCurrency":"ZAR"', false);
        $response->assertSee('"defaultCountry":"ZA"', false);
        $response->assertSee('"squareLocale":"en-ZA"', false);
        $response->assertSee('data-display-currency="ZAR"', false);
        $response->assertSee('value="ZA" selected', false);
    }
}
