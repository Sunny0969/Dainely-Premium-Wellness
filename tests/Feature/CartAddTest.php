<?php

namespace Tests\Feature;

use App\Support\CheckoutCart;
use Tests\TestCase;

class CartAddTest extends TestCase
{
    protected function tearDown(): void
    {
        CheckoutCart::clear();
        parent::tearDown();
    }

    public function test_add_to_cart_json_stays_on_page_and_updates_session(): void
    {
        $response = $this->postJson('/en/cart/add', [
            'product_id' => 'heated-jacket',
            'title'      => 'Heated Jacket',
            'image'      => '/images/jacket.png',
            'price'      => 129.00,
            'quantity'   => 1,
            'option_label' => '2XL',
            'intent'     => 'add',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success'    => true,
            'item_count' => 1,
            'redirect'   => null,
        ]);
        $this->assertSame(1, CheckoutCart::itemCount());
    }

    public function test_checkout_intent_json_returns_checkout_redirect(): void
    {
        $response = $this->postJson('/en/cart/add', [
            'product_id' => 'heated-jacket',
            'title'      => 'Heated Jacket',
            'image'      => '/images/jacket.png',
            'price'      => 129.00,
            'quantity'   => 1,
            'intent'     => 'checkout',
        ]);

        $response->assertOk();
        $response->assertJsonPath('redirect', route('checkout.index', ['locale' => 'en']));
    }

    public function test_add_to_cart_form_redirects_back(): void
    {
        $response = $this->from('/en/products/heated-jacket')->post('/en/cart/add', [
            '_token'     => csrf_token(),
            'product_id' => 'heated-jacket',
            'title'      => 'Heated Jacket',
            'image'      => '/images/jacket.png',
            'price'      => 129.00,
            'quantity'   => 1,
            'intent'     => 'add',
        ]);

        $response->assertRedirect('/en/products/heated-jacket');
        $response->assertSessionHas('success');
    }
}
