<?php

namespace Tests\Feature;

use App\Services\ReviewService;
use App\Services\ShopifyService;
use Mockery;
use Tests\TestCase;

class ProductLandingPageTest extends TestCase
{
    /** @var list<string> */
    public static function premiumProductHandles(): array
    {
        return [
            'dainely-comfort-belt',
            'dainely-ball-massager',
            'neck-pain',
            'back-pain-relief-patches-20-pcs',
            'dainely-unisex-heated-jacket',
            'dainely-foot-massager',
            'brace',
            'dainely-massager',
            'shoulder-brace',
            'stretcher',
            'dainely-orthopedic-back-stretcher',
            'relaxaleg-system',
            'dainely-tourmaline-belt',
            'daily-relief-system',
            'cushion',
            'functional-mushroom-coffee',
        ];
    }

    /** @var list<string> */
    public static function locales(): array
    {
        return ['en', 'fr', 'de'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(ShopifyService::class, function ($mock) {
            $mock->shouldReceive('fetchProductByHandle')
                ->andReturnUsing(fn (string $handle) => [
                    'success' => true,
                    'product' => $this->mockShopifyProduct($handle),
                ]);
        });

        $this->mock(ReviewService::class, function ($mock) {
            $mock->shouldReceive('getCachedStats')
                ->andReturn(['average_rating' => '4.8', 'total_reviews' => 128]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function mockShopifyProduct(string $handle): array
    {
        $variants = str_contains($handle, 'belt')
            ? [
                ['id' => '44123456789', 'price' => '64.00', 'compare_at_price' => '119.00', 'title' => 'S/M'],
                ['id' => '44123456790', 'price' => '64.00', 'compare_at_price' => '119.00', 'title' => 'L/XL'],
                ['id' => '44123456791', 'price' => '64.00', 'compare_at_price' => '119.00', 'title' => '2XL'],
                ['id' => '44123456792', 'price' => '64.00', 'compare_at_price' => '119.00', 'title' => '3XL'],
            ]
            : [
                [
                    'id'                => '44123456789',
                    'price'             => '64.00',
                    'compare_at_price'  => '119.00',
                    'title'             => 'Default',
                ],
            ];

        return [
            'id'         => '8024893456438',
            'handle'     => $handle,
            'title'      => 'Test Product',
            'body_html'  => '<p>Test description</p>',
            'status'     => 'active',
            'vendor'     => 'Dainely',
            'tags'       => '',
            'images'     => [
                ['src' => 'https://cdn.shopify.com/s/files/1/0712/2911/2374/files/test.webp'],
            ],
            'variants'   => $variants,
        ];
    }

    /**
     * @dataProvider premiumProductHandles
     */
    public function test_premium_product_page_renders_in_english(string $handle): void
    {
        $response = $this->get("/en/products/{$handle}");

        $response->assertOk();
        $response->assertDontSee('foreach() argument must be of type array', false);
        $response->assertDontSee('ViewException', false);
        $response->assertSee('Add to Cart', false);
    }

    public function test_patches_product_renders_in_all_locales(): void
    {
        foreach (self::locales() as $locale) {
            $response = $this->get("/{$locale}/products/back-pain-relief-patches-20-pcs");

            $response->assertOk("Failed for locale {$locale}");
            $response->assertDontSee('ViewException', false);
        }
    }

    public function test_belt_size_guide_section_renders_without_error(): void
    {
        $response = $this->get('/en/products/dainely-comfort-belt');

        $response->assertOk();
        $response->assertSee('id="size-guide"', false);
        $response->assertSee('S/M', false);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
