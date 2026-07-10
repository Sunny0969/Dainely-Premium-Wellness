<?php

namespace Tests\Feature;

use App\Support\CheckoutCart;
use Tests\TestCase;

class HeaderCartIconTest extends TestCase
{
    protected function tearDown(): void
    {
        CheckoutCart::clear();
        parent::tearDown();
    }

    public function test_header_shows_cart_link_on_product_pages(): void
    {
        $response = $this->get('/en/products/dainely-belt');

        $response->assertOk();
        $response->assertSee('data-testid="header-cart-link"', false);
        $response->assertSee(__('nav.cart'), false);
    }

    public function test_header_cart_shows_item_count_badge(): void
    {
        CheckoutCart::addItem([
            'product_id' => 'dainely-belt',
            'title'      => 'Dainely Belt',
            'image'      => '/images/dainely-belt-product.png',
            'price'      => 89.00,
            'quantity'   => 1,
        ]);

        CheckoutCart::addItem([
            'product_id' => 'dainely-foot-massager',
            'title'      => 'Foot Massager',
            'image'      => '/images/dainely-belt-product.png',
            'price'      => 59.00,
            'quantity'   => 2,
        ]);

        $response = $this->get('/fr/products');

        $response->assertOk();
        $response->assertSee('data-testid="header-cart-link"', false);
        $response->assertSee('>3<', false);
        $response->assertSee(__('nav.cart_with_count', ['count' => 3]), false);
    }

    public function test_cart_link_returns_to_checkout_with_session_items(): void
    {
        CheckoutCart::addItem([
            'product_id' => 'dainely-belt',
            'title'      => 'Dainely Belt',
            'image'      => '/images/dainely-belt-product.png',
            'price'      => 89.00,
            'quantity'   => 1,
        ]);

        $this->get('/de/products')->assertOk();

        $response = $this->get('/de/checkout');

        $response->assertOk();
        $response->assertSee('Dainely Belt', false);
    }
}
