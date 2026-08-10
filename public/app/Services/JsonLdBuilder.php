<?php

namespace App\Services;

use App\Models\Supabase\AiSchemaCache;
use App\Models\Supabase\LandingPage;
use App\Models\Supabase\Product;
use App\Models\Supabase\ProductContent;
use App\Models\Supabase\ProductKnowledgeSignal;
use App\Support\SupabaseDb;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class JsonLdBuilder
{
    /**
     * Phase 2 §6.2 — full @graph for a product page (locale-aware).
     */
    public function buildForProduct(Product $product, string $locale): array
    {
        $cacheKey = 'product_' . ($product->id ?: ('tmp_' . md5((string) $product->handle))) . "_{$locale}";

        return Cache::remember($cacheKey, 86400, function () use ($product, $locale) {
            $productContent = null;
            if ($product->exists && SupabaseDb::available()) {
                $productContent = SupabaseDb::run(
                    fn () => $product->productContents()->forLocale($locale)->first()
                        ?? $product->productContents()->forLocale('en')->first(),
                    null
                );
            }

            $productUrl = route('products.show', ['locale' => $locale, 'slug' => $product->handle]);
            $description = $productContent?->seo_description
                ?: $productContent?->overview
                ?: $product->title;

            $graphs = [
                $this->makeProductSchema($product, $productContent, $productUrl, $description, $locale),
                $this->makeWebPageSchema($product, $productContent, $productUrl, $description, $locale),
                $this->makeBreadcrumbSchema($product, $productUrl, $locale),
                $this->makeOrganizationSchema(),
            ];

            $faqPage = $this->makeFaqPageSchemaFromProduct($product, $locale);
            if ($faqPage !== null) {
                $graphs[] = $faqPage;
            }

            $rating = $this->makeAggregateRatingSchema($product->handle ?? '');
            if ($rating !== null) {
                // Attach rating onto Product node when possible
                foreach ($graphs as &$node) {
                    if (($node['@type'] ?? null) === 'Product') {
                        $node['aggregateRating'] = $rating;
                        break;
                    }
                }
                unset($node);
            }

            $schema = [
                '@context' => 'https://schema.org',
                '@graph'   => array_values(array_filter($graphs)),
            ];

            if ($product->exists && SupabaseDb::available()) {
                SupabaseDb::run(function () use ($product, $locale, $schema) {
                    AiSchemaCache::updateOrCreate(
                        [
                            'cacheable_type' => Product::class,
                            'cacheable_id'   => $product->id,
                            'locale'         => $locale,
                        ],
                        [
                            'schema_data'    => $schema,
                            'schema_version' => '1.0',
                            'generated_at'   => now(),
                        ]
                    );
                });
            }

            return $schema;
        });
    }

    /**
     * Phase 2 §6.2 — @graph for a landing page.
     */
    public function buildForLandingPage(LandingPage $page, string $locale): array
    {
        $cacheKey = "landing_{$page->id}_{$locale}";

        return Cache::remember($cacheKey, 86400, function () use ($page, $locale) {
            $url = url('/' . $locale . '/' . ltrim((string) $page->slug, '/'));

            $graphs = [
                [
                    '@type'       => 'WebPage',
                    '@id'         => $url . '#webpage',
                    'url'         => $url,
                    'name'        => $page->meta_title ?: $page->title,
                    'description' => $page->meta_description ?: $page->title,
                    'inLanguage'  => $locale,
                ],
                $this->makeOrganizationSchema(),
                [
                    '@type'           => 'BreadcrumbList',
                    '@id'             => $url . '#breadcrumb',
                    'itemListElement' => [
                        [
                            '@type'    => 'ListItem',
                            'position' => 1,
                            'name'     => 'Home',
                            'item'     => route('home', ['locale' => $locale]),
                        ],
                        [
                            '@type'    => 'ListItem',
                            'position' => 2,
                            'name'     => $page->title,
                            'item'     => $url,
                        ],
                    ],
                ],
            ];

            $faqs = $page->faqs()
                ->approved()
                ->forLocale($locale)
                ->orderBy('sort_order')
                ->get();

            $faqSchema = $this->makeFaqPageSchema($faqs, $locale);
            if ($faqSchema !== null) {
                $graphs[] = $faqSchema;
            }

            return [
                '@context' => 'https://schema.org',
                '@graph'   => $graphs,
            ];
        });
    }

    /**
     * Backward-compatible string helper used by ProductController / FAQ page.
     */
    public function buildProductSchema(Product $product, ?ProductContent $content = null): string
    {
        $locale = app()->getLocale();

        // Prefer fresh overlay passed from the controller (avoids stale 24h cache after Admin edits)
        if ($content !== null) {
            $productUrl = route('products.show', ['locale' => $locale, 'slug' => $product->handle]);
            $description = (is_string($content->seo_description) && trim($content->seo_description) !== '')
                ? trim($content->seo_description)
                : ((is_string($content->overview) && trim($content->overview) !== '')
                    ? trim($content->overview)
                    : (string) $product->title);
            $name = (is_string($content->seo_title) && trim($content->seo_title) !== '')
                ? trim($content->seo_title)
                : (string) $product->title;

            $schema = [
                '@context' => 'https://schema.org',
                '@graph' => array_values(array_filter([
                    $this->makeProductSchema($product, $content, $productUrl, $description, $locale),
                    $this->makeWebPageSchema($product, $content, $productUrl, $description, $locale),
                    $this->makeBreadcrumbSchema($product, $productUrl, $locale),
                    $this->makeOrganizationSchema(),
                ])),
            ];

            // Keep Product name in sync with overlay SEO title when set
            foreach ($schema['@graph'] as &$node) {
                if (($node['@type'] ?? null) === 'Product' && $name !== '') {
                    $node['name'] = $name;
                }
                if (($node['@type'] ?? null) === 'WebPage' && $name !== '') {
                    $node['name'] = $name;
                }
            }
            unset($node);

            return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        $schema = $this->buildForProduct($product, $locale);

        return json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function buildFaqSchema(Collection $faqs): string
    {
        $schema = $this->makeFaqPageSchema($faqs, app()->getLocale()) ?? [
            '@type'      => 'FAQPage',
            'mainEntity' => [],
        ];

        $encoded = [
            '@context'   => 'https://schema.org',
            '@type'      => $schema['@type'] ?? 'FAQPage',
            'mainEntity' => $schema['mainEntity'] ?? [],
        ];

        return json_encode($encoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    public function makeOrganizationSchema(): array
    {
        return [
            '@type' => 'Organization',
            '@id'   => rtrim(config('app.url'), '/') . '/#organization',
            'name'  => config('app.name', 'Dainely'),
            'url'   => config('app.url'),
            'logo'  => rtrim(config('app.url'), '/') . '/images/logo.png',
        ];
    }

    /** @deprecated Use makeOrganizationSchema() */
    public function buildOrganizationSchema(): array
    {
        return $this->makeOrganizationSchema();
    }

    protected function makeProductSchema(
        Product $product,
        ?ProductContent $content,
        string $productUrl,
        string $description,
        string $locale
    ): array {
        $node = [
            '@type'       => 'Product',
            '@id'         => $productUrl . '#product',
            'name'        => $content?->seo_title ?: $product->title,
            'description' => strip_tags((string) $description),
            'sku'         => $product->sku ?: $product->handle,
            'url'         => $productUrl,
            'inLanguage'  => $locale,
        ];

        if ($product->featured_image) {
            $node['image'] = [$product->featured_image];
        }

        if ($product->price !== null) {
            $node['offers'] = [
                '@type'         => 'Offer',
                'url'           => $productUrl,
                'priceCurrency' => config('shopify.shop_currency', 'USD'),
                'price'         => (string) $product->price,
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability'  => $product->status === 'active'
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
            ];
        }

        return $node;
    }

    protected function makeWebPageSchema(
        Product $product,
        ?ProductContent $content,
        string $productUrl,
        string $description,
        string $locale
    ): array {
        return [
            '@type'       => 'WebPage',
            '@id'         => $productUrl . '#webpage',
            'url'         => $productUrl,
            'name'        => $content?->seo_title ?: $product->title,
            'description' => strip_tags((string) $description),
            'isPartOf'    => ['@id' => rtrim(config('app.url'), '/') . '/#website'],
            'about'       => ['@id' => $productUrl . '#product'],
            'inLanguage'  => $locale,
        ];
    }

    protected function makeBreadcrumbSchema(Product $product, string $productUrl, string $locale): array
    {
        $trail = app(\App\Services\BreadcrumbBuilder::class)->forProduct(
            $locale,
            (string) $product->title,
            $productUrl
        );

        return app(\App\Services\BreadcrumbBuilder::class)->toSchema($trail, $productUrl);
    }

    public function makeFaqPageSchema(Collection $faqs, string $locale): ?array
    {
        $entities = $faqs->map(function ($faq) use ($locale) {
            $question = $faq->question ?? null;
            $answer = $faq->answer ?? null;
            if (! $question || ! $answer) {
                return null;
            }
            // Skip if FAQ has locale and it doesn't match (when mixed collections)
            if (isset($faq->locale) && $faq->locale !== $locale && method_exists($faq, 'getAttribute')) {
                // allow plain objects without locale
            }

            return [
                '@type'          => 'Question',
                'name'           => $question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => strip_tags((string) $answer),
                ],
            ];
        })->filter()->values()->all();

        if ($entities === []) {
            return null;
        }

        return [
            '@type'      => 'FAQPage',
            'mainEntity' => $entities,
        ];
    }

    protected function makeFaqPageSchemaFromProduct(Product $product, string $locale): ?array
    {
        if (! $product->exists || ! SupabaseDb::available()) {
            return null;
        }

        $faqs = SupabaseDb::run(
            fn () => $product->faqs()
                ->approved()
                ->forLocale($locale)
                ->orderBy('sort_order')
                ->get(),
            collect()
        );

        $entities = [];
        $faqSchema = $this->makeFaqPageSchema($faqs, $locale);
        if ($faqSchema) {
            $entities = $faqSchema['mainEntity'];
        }

        $signals = SupabaseDb::run(
            fn () => ProductKnowledgeSignal::query()
                ->where('product_id', $product->id)
                ->forLocale($locale)
                ->approved()
                ->get(),
            collect()
        );

        foreach ($signals as $signal) {
            $entities[] = [
                '@type'          => 'Question',
                'name'           => $signal->question,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => strip_tags((string) $signal->answer),
                ],
            ];
        }

        if ($entities === []) {
            return null;
        }

        return [
            '@type'      => 'FAQPage',
            '@id'        => route('products.show', ['locale' => $locale, 'slug' => $product->handle]) . '#faq',
            'mainEntity' => $entities,
        ];
    }

    /**
     * Judge.me cached stats → AggregateRating (no blocking API call).
     */
    protected function makeAggregateRatingSchema(string $handle): ?array
    {
        if ($handle === '') {
            return null;
        }

        $stats = app(ReviewService::class)->getCachedStats($handle);
        $count = (int) ($stats['total_reviews'] ?? 0);
        $avg = (float) ($stats['average_rating'] ?? 0);

        if ($count < 1 || $avg <= 0) {
            return null;
        }

        return [
            '@type'       => 'AggregateRating',
            'ratingValue' => (string) round($avg, 1),
            'reviewCount' => (string) $count,
            'bestRating'  => '5',
            'worstRating' => '1',
        ];
    }
}
