<?php

namespace Tests\Feature;

use App\Services\ReviewService;
use App\Services\ShopifyService;
use App\Support\CheckoutCart;
use Mockery;
use Tests\TestCase;

class SiteSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(ShopifyService::class, function ($mock) {
            $mock->shouldReceive('fetchProducts')->andReturn([
                'success'  => true,
                'products' => [[
                    'id'       => '1',
                    'handle'   => 'dainely-comfort-belt',
                    'title'    => 'Dainely Belt',
                    'status'   => 'active',
                    'variants' => [['price' => '64.00', 'compare_at_price' => '119.00']],
                    'images'   => [['src' => 'https://cdn.shopify.com/test.webp']],
                ]],
                'source'   => 'mock',
            ]);
            $mock->shouldReceive('fetchProductByHandle')->andReturn([
                'success' => true,
                'product' => [
                    'id'        => '1',
                    'handle'    => 'dainely-comfort-belt',
                    'title'     => 'Dainely Belt',
                    'body_html' => '<p>Belt</p>',
                    'status'    => 'active',
                    'variants'  => [['price' => '64.00']],
                    'images'    => [['src' => 'https://cdn.shopify.com/test.webp']],
                ],
            ]);
            $mock->shouldReceive('mapProductsForDisplay')->andReturnUsing(fn ($products) => $products);
            $mock->shouldReceive('mapProductForCta')->andReturnUsing(fn ($product) => $product);
        });

        $this->mock(ReviewService::class, function ($mock) {
            $mock->shouldReceive('getCachedStats')->andReturn(['average_rating' => '4.8', 'total_reviews' => 50]);
            $mock->shouldReceive('getCachedStatsForHandles')->andReturn([]);
        });
    }

    protected function tearDown(): void
    {
        CheckoutCart::clear();
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @dataProvider publicPages
     */
    public function test_public_pages_return_ok(string $path): void
    {
        $response = $this->get($path);

        $response->assertOk();
        $response->assertDontSee('ViewException', false);
    }

    public static function publicPages(): array
    {
        return [
            ['en/'],
            ['en/products'],
            ['en/about'],
            ['en/faq'],
            ['en/contact'],
            ['en/blog'],
            ['en/education/back-pain'],
            ['fr/'],
            ['de/'],
        ];
    }

    public function test_checkout_page_renders_with_cart_item(): void
    {
        CheckoutCart::add([
            'product_id' => 'belt',
            'title'      => 'Dainely Belt',
            'image'      => '/images/dainely-belt-product.png',
            'price'      => 64.00,
            'quantity'   => 1,
        ]);

        $response = $this->get('/en/checkout');

        $response->assertOk();
        $response->assertSee('Dainely Belt', false);
        $response->assertDontSee('ViewException', false);
    }
}
