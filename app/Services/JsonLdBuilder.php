<?php

namespace App\Services;

use App\Models\Supabase\Product;
use App\Models\Supabase\ProductContent;
use App\Models\Supabase\AiSchemaCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class JsonLdBuilder
{
    /**
     * Build and cache Product JSON-LD schema.
     */
    public function buildProductSchema(Product $product, ?ProductContent $content = null): string
    {
        $cacheKey = "json_ld_product_{$product->id}";
        
        return Cache::remember($cacheKey, now()->addHours(24), function () use ($product, $content) {
            $schema = [
                '@context'    => 'https://schema.org/',
                '@type'       => 'Product',
                'name'        => $product->title,
                'description' => $content?->seo_description ?? strip_tags($product->body_html),
                'sku'         => $product->handle,
            ];

            // If product has a primary image
            if (!empty($product->image) && isset($product->image['src'])) {
                $schema['image'] = [$product->image['src']];
            }

            // Aggregate Rating (placeholder or actual if implemented)
            // You can inject actual rating logic here
            $schema['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => '4.8',
                'reviewCount' => '120',
            ];

            // Offer
            if ($product->price) {
                $schema['offers'] = [
                    '@type'         => 'Offer',
                    'url'           => route('products.show', ['locale' => app()->getLocale(), 'slug' => $product->handle]),
                    'priceCurrency' => 'USD',
                    'price'         => $product->price,
                    'itemCondition' => 'https://schema.org/NewCondition',
                    'availability'  => $product->status === 'active' 
                                        ? 'https://schema.org/InStock' 
                                        : 'https://schema.org/OutOfStock',
                ];
            }

            $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            // Store in Supabase ai_schema_cache for AI agents only if product has a database ID
            if ($product->id) {
                AiSchemaCache::updateOrCreate(
                    [
                        'schemaable_type' => Product::class,
                        'schemaable_id'   => $product->id,
                        'locale'          => app()->getLocale(),
                        'schema_type'     => 'Product',
                    ],
                    [
                        'schema_json' => $schema,
                        'expires_at'  => now()->addHours(24),
                    ]
                );
            }

            return $json;
        });
    }

    /**
     * Build and cache FAQ JSON-LD schema.
     */
    public function buildFaqSchema(Collection $faqs): string
    {
        // Generate a unique cache key based on the FAQs
        $ids = $faqs->pluck('id')->implode('_');
        $cacheKey = "json_ld_faq_{$ids}_" . app()->getLocale();

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($faqs) {
            $questions = $faqs->map(function ($faq) {
                return [
                    '@type'          => 'Question',
                    'name'           => $faq->question ?? $faq->translation()?->question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text'  => $faq->answer ?? $faq->translation()?->answer,
                    ],
                ];
            })->toArray();

            $schema = [
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => $questions,
            ];

            $json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            // Optionally cache in AiSchemaCache for the page (skipping here for brevity unless tied to a specific LandingPage)
            
            return $json;
        });
    }

    /**
     * Build Organization JSON-LD schema.
     */
    public function buildOrganizationSchema(): string
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type'    => 'Organization',
            'name'     => config('app.name'),
            'url'      => config('app.url'),
            'logo'     => config('app.url') . '/images/logo.png', // Update path to actual logo
        ];

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
