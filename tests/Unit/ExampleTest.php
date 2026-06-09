<?php

namespace Tests\Unit;

use App\Services\ShopifyService;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_variant_id_normalization_rejects_invalid_values(): void
    {
        $shopify = app(ShopifyService::class);

        $this->assertNull($shopify->normalizeVariantId(null));
        $this->assertNull($shopify->normalizeVariantId(''));
        $this->assertNull($shopify->normalizeVariantId('invalid'));
        $this->assertNull($shopify->normalizeVariantId('0'));
        $this->assertSame(123456, $shopify->normalizeVariantId('123456'));
    }
}
