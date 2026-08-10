<?php

namespace App\Services;

use Illuminate\Support\Facades\App;

class SeoService
{
    protected array $data = [];

    protected array $defaults = [
        'title'       => 'Dainely — Premium Wellness Solutions for Back Pain & Sciatica',
        'description' => 'Discover Dainely\'s medical-grade wellness products. Clinically developed for back pain, sciatica, posture correction and lasting mobility.',
        'image'       => '/images/og-default.jpg',
        'type'        => 'website',
    ];

    /**
     * Set SEO metadata for the current page.
     */
    public function set(array $data): static
    {
        $this->data = array_merge($this->defaults, $data);
        return $this;
    }

    public function title(): string
    {
        return $this->data['title'] ?? $this->defaults['title'];
    }

    public function description(): string
    {
        return $this->data['description'] ?? $this->defaults['description'];
    }

    public function image(): string
    {
        return $this->data['image'] ?? $this->defaults['image'];
    }

    public function type(): string
    {
        return $this->data['type'] ?? 'website';
    }

    public function canonical(): string
    {
        return $this->data['canonical'] ?? url()->current();
    }

    /**
     * Generate hreflang link tags for multilingual SEO.
     * Returns array of [locale => url] pairs.
     */
    public function hreflangLinks(string $routeName, array $params = []): array
    {
        $locales = config('app.supported_locales', ['en', 'fr', 'de']);
        $links   = [];

        foreach ($locales as $locale) {
            try {
                $links[$locale] = route($routeName, array_merge(['locale' => $locale], $params));
            } catch (\Exception $e) {
                // Route may not exist for all locales — skip silently
            }
        }

        return $links;
    }

    /**
     * Generate Product schema.org JSON-LD.
     */
    public function productSchema(array $product, array $reviews = []): string
    {
        $schema = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product['name'] ?? '',
            'description' => $product['description'] ?? '',
            'image'       => $product['image'] ?? '',
            'brand'       => [
                '@type' => 'Brand',
                'name'  => 'Dainely',
            ],
            'offers' => [
                '@type'         => 'Offer',
                'priceCurrency' => $product['currency'] ?? 'USD',
                'price'         => $product['price'] ?? '',
                'availability'  => 'https://schema.org/InStock',
                'url'           => $product['url'] ?? url()->current(),
            ],
        ];

        if (!empty($reviews)) {
            $schema['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => $reviews['average'] ?? 5,
                'reviewCount' => $reviews['count'] ?? 0,
            ];
        }

        return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Generate FAQPage schema.org JSON-LD.
     */
    public function faqSchema(array $faqs): string
    {
        $items = array_map(fn($faq) => [
            '@type'          => 'Question',
            'name'           => $faq['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $faq['answer'],
            ],
        ], $faqs);

        $schema = [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => $items,
        ];

        return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    /**
     * Generate Article schema.org JSON-LD for blog posts.
     */
    public function articleSchema(array $post): string
    {
        $schema = [
            '@context'         => 'https://schema.org',
            '@type'            => 'Article',
            'headline'         => $post['title'] ?? '',
            'description'      => $post['excerpt'] ?? '',
            'image'            => $post['image'] ?? '',
            'datePublished'    => $post['published_at'] ?? '',
            'dateModified'     => $post['updated_at'] ?? '',
            'author'           => [
                '@type' => 'Person',
                'name'  => $post['author_name'] ?? 'Dainely Team',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name'  => 'Dainely',
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => asset('images/logo.png'),
                ],
            ],
        ];

        return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }
}
