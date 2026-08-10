<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Static product data for running the storefront without a database.
 */
class StaticCatalog
{
    public static function featuredProduct(): object
    {
        return self::products()->first();
    }

    public static function products(): Collection
    {
        return collect([
            self::product(
                id: 1,
                sku: 'DNB-001',
                priceUsd: 89.00,
                comparePriceUsd: 119.00,
                isFeatured: true,
                type: 'simple',
                mainImage: 'images/dainely-belt-product.png',
                translations: [
                    ['locale' => 'en', 'name' => 'Dainely Belt', 'slug' => 'dainely-belt', 'short_description' => 'Medical-grade lumbar decompression belt targeting sciatic nerve relief and posture correction.'],
                    ['locale' => 'fr', 'name' => 'Ceinture Dainely', 'slug' => 'ceinture-dainely', 'short_description' => 'Ceinture de décompression lombaire médicale.'],
                    ['locale' => 'de', 'name' => 'Dainely Gürtel', 'slug' => 'dainely-guertel', 'short_description' => 'Medizinischer Lendenwirbelstützen-Gürtel.'],
                ]
            ),
            self::product(
                id: 2,
                sku: 'DRS-001',
                priceUsd: 149.00,
                comparePriceUsd: 189.00,
                isFeatured: true,
                type: 'bundle',
                mainImage: 'images/daily-relief-system.png',
                translations: [
                    ['locale' => 'en', 'name' => 'Daily Relief System', 'slug' => 'daily-relief-system', 'short_description' => 'Complete wellness protocol: Dainely Belt + foam roller + resistance bands + recovery guide.'],
                    ['locale' => 'fr', 'name' => 'Système de Soulagement Quotidien', 'slug' => 'systeme-soulagement-quotidien', 'short_description' => 'Protocole bien-être complet.'],
                    ['locale' => 'de', 'name' => 'Tägliches Linderungs-System', 'slug' => 'taegliches-linderungs-system', 'short_description' => 'Vollständiges Wellness-Protokoll.'],
                ]
            ),
        ]);
    }

    public static function findBySlug(string $slug, ?string $locale = null): ?object
    {
        $locale = $locale ?? app()->getLocale();

        foreach (self::products() as $product) {
            $translation = $product->translation($locale);
            if ($translation && $translation->slug === $slug) {
                return $product;
            }
        }

        foreach (self::products() as $product) {
            $translation = $product->translation('en');
            if ($translation && $translation->slug === $slug) {
                return $product;
            }
        }

        return null;
    }

    protected static function product(
        int $id,
        string $sku,
        float $priceUsd,
        float $comparePriceUsd,
        bool $isFeatured,
        string $type,
        string $mainImage,
        array $translations
    ): object {
        $translations = collect(array_map(fn (array $t) => (object) $t, $translations));

        return new class ($id, $sku, $priceUsd, $comparePriceUsd, $isFeatured, $type, $mainImage, $translations) {
            public function __construct(
                public int $id,
                public string $sku,
                public float $price_usd,
                public float $compare_price_usd,
                public bool $is_featured,
                public string $type,
                public string $main_image,
                private Collection $translations,
            ) {}

            public function translation(?string $locale = null): ?object
            {
                $locale = $locale ?? app()->getLocale();

                return $this->translations->firstWhere('locale', $locale)
                    ?? $this->translations->firstWhere('locale', 'en');
            }

            public function getSavingsPercentAttribute(): int
            {
                if (!$this->compare_price_usd || $this->compare_price_usd <= $this->price_usd) {
                    return 0;
                }

                return (int) round((($this->compare_price_usd - $this->price_usd) / $this->compare_price_usd) * 100);
            }

            public function __get(string $key): mixed
            {
                if ($key === 'savings_percent') {
                    return $this->getSavingsPercentAttribute();
                }

                return $this->{$key} ?? null;
            }
        };
    }
}
