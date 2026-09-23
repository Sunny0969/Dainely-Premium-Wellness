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
    public function buildProductSchema(Product $product, ?ProductContent $content = null, array $shopifyProduct = []): string
    {
        $locale = app()->getLocale();
        $productUrl = route('products.show', ['locale' => $locale, 'slug' => $product->handle]);

        // 1. Get base organization config from json_ld_schemas if it exists
        $orgName = 'Dainely';
        $orgLogo = 'https://dainely.com/images/Dainelycut.png';
        $brandName = 'Dainely';

        $filePath = storage_path('app/json_ld_schemas.json');
        if (file_exists($filePath)) {
            $schemas = json_decode(file_get_contents($filePath), true) ?: [];
            $first = $schemas[0] ?? [];
            if (!empty($first['org_name'])) $orgName = $first['org_name'];
            if (!empty($first['org_logo'])) $orgLogo = $first['org_logo'];
            if (!empty($first['brand'])) $brandName = $first['brand'];
        }

        // 2. Automate Product data extraction
        $description = (is_string($content?->seo_description) && trim($content->seo_description) !== '')
            ? trim($content->seo_description)
            : ((is_string($content?->overview) && trim($content->overview) !== '')
                ? trim($content->overview)
                : (string) $product->title);
        $name = (is_string($content?->seo_title) && trim($content->seo_title) !== '')
            ? trim($content->seo_title)
            : (string) $product->title;

        // Generate Offers
        $offers = [];
        $variants = $shopifyProduct['variants'] ?? [];
        if (empty($variants) && $product->price !== null) {
            $offers[] = [
                '@type'         => 'Offer',
                'name'          => $name,
                'url'           => $productUrl,
                'priceCurrency' => config('shopify.shop_currency', 'USD'),
                'price'         => (string) $product->price,
                'itemCondition' => 'https://schema.org/NewCondition',
                'availability'  => $product->status === 'active' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller'        => ['@id' => 'https://dainely.com/#organization']
            ];
        } else {
            foreach ($variants as $v) {
                $offers[] = [
                    '@type'         => 'Offer',
                    'name'          => trim($name . ' - ' . ($v['title'] ?? '')),
                    'url'           => $productUrl,
                    'priceCurrency' => config('shopify.shop_currency', 'USD'),
                    'price'         => (string) ($v['price'] ?? 0),
                    'itemCondition' => 'https://schema.org/NewCondition',
                    'availability'  => 'https://schema.org/InStock',
                    'seller'        => ['@id' => 'https://dainely.com/#organization']
                ];
            }
        }

        $graph = [
            [
                '@type' => 'Organization',
                '@id' => 'https://dainely.com/#organization',
                'name' => $orgName,
                'url' => 'https://dainely.com',
                'logo' => $orgLogo
            ],
            [
                '@type' => 'WebSite',
                '@id' => 'https://dainely.com/#website',
                'url' => 'https://dainely.com',
                'name' => $orgName,
                'publisher' => ['@id' => 'https://dainely.com/#organization']
            ],
            [
                '@type' => 'Brand',
                '@id' => 'https://dainely.com/#brand',
                'name' => $brandName
            ],
            [
                '@type' => 'WebPage',
                '@id' => $productUrl . '#webpage',
                'url' => $productUrl,
                'name' => $name,
                'description' => strip_tags((string) $description),
                'isPartOf' => ['@id' => 'https://dainely.com/#website'],
                'mainEntity' => ['@id' => $productUrl . '#product'],
                'inLanguage' => $locale
            ],
            [
                '@type' => 'Product',
                '@id' => $productUrl . '#product',
                'name' => $name,
                'description' => strip_tags((string) $description),
                'sku' => $product->sku ?: $product->handle,
                'url' => $productUrl,
                'image' => $product->featured_image ? [$product->featured_image] : [],
                'brand' => ['@id' => 'https://dainely.com/#brand'],
                'category' => !empty($shopifyProduct['product_type']) ? $shopifyProduct['product_type'] : 'Wellness Products',
                'material' => 'Breathable, Lightweight Stretch Support Fabric',
                'color' => 'Black',
                'mainEntityOfPage' => ['@id' => $productUrl . '#webpage'],
                'offers' => $offers
            ],
            [
                '@type' => 'BreadcrumbList',
                '@id' => $productUrl . '#breadcrumb',
                'itemListElement' => [
                    [ '@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home', ['locale' => $locale]) ],
                    [ '@type' => 'ListItem', 'position' => 2, 'name' => 'Products', 'item' => route('products.index', ['locale' => $locale]) ],
                    [ '@type' => 'ListItem', 'position' => 3, 'name' => $name, 'item' => $productUrl ]
                ]
            ]
        ];

        $rating = $this->makeAggregateRatingSchema($product->handle ?? '');
        if ($rating !== null) {
            foreach ($graph as &$node) {
                if (($node['@type'] ?? null) === 'Product') {
                    $node['aggregateRating'] = $rating;
                    break;
                }
            }
            unset($node);
        }

        $faqPage = $this->makeFaqPageSchemaFromProduct($product, $locale);
        if ($faqPage !== null) {
            // Fix ID of FAQ page to match product structure
            $faqPage['@id'] = $productUrl . '#faq';
            $faqPage['url'] = $productUrl . '#faq';
            $graph[] = $faqPage;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => $graph
        ];

        return json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
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
            '@id'   => 'https://dainely.com/#organization',
            'name'  => 'Dainely',
            'url'   => 'https://dainely.com',
            'logo'  => 'https://dainely.com/images/logo.png',
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
            'isPartOf'    => ['@id' => 'https://dainely.com/#website'],
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

        $entities = [];

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

        if (empty($entities)) {
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
