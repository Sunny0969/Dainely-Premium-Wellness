<?php

namespace Tests\Unit;

use App\Support\CheckoutCart;
use Tests\TestCase;

class CheckoutCartTest extends TestCase
{
    public function test_add_item_appends_second_product_instead_of_replacing(): void
    {
        CheckoutCart::clear();

        CheckoutCart::addItem([
            'product_id' => 'belt',
            'title'      => 'Dainely Belt',
            'image'      => '/img/belt.png',
            'price'      => 89.00,
            'quantity'   => 1,
        ]);

        CheckoutCart::addItem([
            'product_id' => 'stretcher',
            'title'      => 'Neck Stretcher',
            'image'      => '/img/stretcher.png',
            'price'      => 39.95,
            'quantity'   => 1,
        ]);

        $items = CheckoutCart::getItems();

        $this->assertCount(2, $items);
        $this->assertSame('Dainely Belt', $items[0]['title']);
        $this->assertSame('Neck Stretcher', $items[1]['title']);
    }

    public function test_add_same_variant_increments_quantity(): void
    {
        CheckoutCart::clear();

        CheckoutCart::addItem([
            'product_id' => 'belt',
            'title'      => 'Dainely Belt',
            'image'      => '/img/belt.png',
            'price'      => 89.00,
            'variant_id' => '123',
            'quantity'   => 1,
        ]);

        CheckoutCart::addItem([
            'product_id' => 'belt',
            'title'      => 'Dainely Belt',
            'image'      => '/img/belt.png',
            'price'      => 89.00,
            'variant_id' => '123',
            'quantity'   => 2,
        ]);

        $items = CheckoutCart::getItems();

        $this->assertCount(1, $items);
        $this->assertSame(3, $items[0]['quantity']);
    }
}
