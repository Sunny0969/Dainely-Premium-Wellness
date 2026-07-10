<?php

namespace App\Support;

class CheckoutCart
{
    public const SESSION_KEY = 'checkout.cart_v2';

    public const LEGACY_KEY = 'checkout.cart';

    /**
     * @return list<array<string, mixed>>
     */
    public static function getItems(): array
    {
        self::migrateLegacy();

        $cart = session(self::SESSION_KEY);
        if (! is_array($cart) || ! is_array($cart['items'] ?? null)) {
            return [];
        }

        return array_values(array_map(
            fn (array $item) => self::normalizeItem($item),
            $cart['items']
        ));
    }

    /**
     * Backward-compatible single-item accessor (first line).
     *
     * @return array<string, mixed>
     */
    public static function get(): array
    {
        $items = self::getItems();

        return $items[0] ?? self::defaultItem();
    }

    /**
     * @return array{items: list<array<string, mixed>>}
     */
    public static function getCart(): array
    {
        return ['items' => self::getItems()];
    }

    public static function exists(): bool
    {
        return self::getItems() !== [];
    }

    public static function itemCount(): int
    {
        return array_sum(array_map(
            fn (array $item) => max(1, (int) ($item['quantity'] ?? 1)),
            self::getItems()
        ));
    }

    /**
     * Add or merge a line item (same product + variant increments quantity).
     *
     * @param  array<string, mixed>  $item
     */
    public static function addItem(array $item): void
    {
        $items = self::getItems();
        $normalized = self::normalizeItem($item);
        $key = $normalized['key'];

        foreach ($items as $index => $existing) {
            if (($existing['key'] ?? '') === $key) {
                $items[$index]['quantity'] = max(1, (int) $existing['quantity']) + max(1, (int) $normalized['quantity']);
                self::persist($items);

                return;
            }
        }

        $items[] = $normalized;
        self::persist($items);
    }

    /**
     * Replace entire cart with one item (legacy).
     *
     * @param  array<string, mixed>  $item
     */
    public static function put(array $item): void
    {
        self::persist([self::normalizeItem($item)]);
    }

    /**
     * @param  array<string, int>  $quantities  item key => qty
     */
    public static function updateQuantities(array $quantities): void
    {
        $items = self::getItems();
        $updated = [];

        foreach ($items as $item) {
            $key = $item['key'] ?? '';
            if (! array_key_exists($key, $quantities)) {
                $updated[] = $item;
                continue;
            }

            $qty = max(0, (int) $quantities[$key]);
            if ($qty > 0) {
                $item['quantity'] = min($qty, 20);
                $updated[] = $item;
            }
        }

        self::persist($updated);
    }

    public static function removeItem(string $key): void
    {
        $items = array_values(array_filter(
            self::getItems(),
            fn (array $item) => ($item['key'] ?? '') !== $key
        ));
        self::persist($items);
    }

    public static function clear(): void
    {
        session()->forget([self::SESSION_KEY, self::LEGACY_KEY]);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    protected static function persist(array $items): void
    {
        session([self::SESSION_KEY => ['items' => array_values($items)]]);
        session()->forget(self::LEGACY_KEY);
    }

    protected static function migrateLegacy(): void
    {
        $cart = session(self::SESSION_KEY);
        if (is_array($cart) && is_array($cart['items'] ?? null) && $cart['items'] !== []) {
            return;
        }

        $legacy = session(self::LEGACY_KEY);
        if (! is_array($legacy) || empty($legacy['title'])) {
            return;
        }

        self::persist([self::normalizeItem($legacy)]);
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected static function normalizeItem(array $item): array
    {
        $productId = (string) ($item['product_id'] ?? 'unknown');
        $variantId = $item['variant_id'] ?? null;
        $key       = (string) ($item['key'] ?? self::makeKey($productId, $variantId));

        return [
            'key'              => $key,
            'product_id'       => $productId,
            'title'            => (string) ($item['title'] ?? 'Product'),
            'subtitle'         => $item['subtitle'] ?? null,
            'image'            => (string) ($item['image'] ?? asset('images/dainely-belt-product.png')),
            'price'            => round((float) ($item['price'] ?? 0), 2),
            'compare_at_price' => isset($item['compare_at_price']) ? round((float) $item['compare_at_price'], 2) : null,
            'quantity'         => max(1, min(20, (int) ($item['quantity'] ?? 1))),
            'option_label'     => $item['option_label'] ?? null,
            'option_value'     => $item['option_value'] ?? null,
            'variant_id'       => $item['variant_id'] ?? null,
            'sku'              => $item['sku'] ?? null,
            'source'           => $item['source'] ?? 'shopify',
        ];
    }

    public static function makeKey(string $productId, mixed $variantId): string
    {
        $variant = $variantId !== null && $variantId !== '' ? (string) $variantId : 'default';

        return $productId . ':' . $variant;
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultItem(): array
    {
        return self::normalizeItem([
            'product_id'       => 'dainely-belt',
            'title'            => 'Dainely Belt',
            'subtitle'         => 'Premium Lumbar Support',
            'image'            => asset('images/dainely-belt-product.png'),
            'price'            => 89.00,
            'compare_at_price' => 119.00,
            'quantity'         => 1,
            'source'           => 'static',
        ]);
    }
}
