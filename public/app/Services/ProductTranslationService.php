<?php

namespace App\Services;

class ProductTranslationService
{
    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    public function apply(array $product, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        if ($locale === 'en') {
            return $product;
        }

        $handle = $this->normalizeHandle((string) ($product['handle'] ?? ''));
        $entry  = $this->lookup($handle, $locale);

        if ($entry === null) {
            return $product;
        }

        $copy = $product;

        if (! empty($entry['title'])) {
            $copy['title'] = (string) $entry['title'];
        }

        if (! empty($entry['description'])) {
            $copy['body_html'] = (string) $entry['description'];
        }

        return $copy;
    }

    /**
     * @param  list<array<string, mixed>>  $products
     * @return list<array<string, mixed>>
     */
    public function applyMany(array $products, ?string $locale = null): array
    {
        return array_values(array_map(
            fn (array $product) => $this->apply($product, $locale),
            $products
        ));
    }

    public function titleForHandle(string $handle, string $fallback, ?string $locale = null): string
    {
        $entry = $this->lookup($this->normalizeHandle($handle), $locale ?? app()->getLocale());

        return is_array($entry) && ! empty($entry['title'])
            ? (string) $entry['title']
            : $fallback;
    }

    /**
     * @return array{title?: string, description?: string}|null
     */
    protected function lookup(string $handle, string $locale): ?array
    {
        /** @var array<string, array{title?: string, description?: string}> $catalog */
        $catalog = trans('catalog.products', [], $locale);

        if (! is_array($catalog)) {
            return null;
        }

        if (isset($catalog[$handle]) && is_array($catalog[$handle])) {
            return $catalog[$handle];
        }

        foreach ($this->handleAliases($handle) as $alias) {
            if (isset($catalog[$alias]) && is_array($catalog[$alias])) {
                return $catalog[$alias];
            }
        }

        return null;
    }

    protected function normalizeHandle(string $handle): string
    {
        $handle = strtolower(trim(urldecode($handle)));

        return str_replace('™', '', $handle);
    }

    /**
     * @return list<string>
     */
    protected function handleAliases(string $handle): array
    {
        $aliases = [$handle];
        $stripped = preg_replace('/[^a-z0-9-]/', '', $handle) ?? $handle;

        if ($stripped !== $handle) {
            $aliases[] = $stripped;
        }

        return array_values(array_unique($aliases));
    }
}
