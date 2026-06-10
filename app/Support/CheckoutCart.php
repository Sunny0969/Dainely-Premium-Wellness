<?php

namespace App\Support;

class CheckoutCart
{
    public const SESSION_KEY = 'checkout.cart_items';

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'product_id'       => 'dainely-belt',
            'title'            => 'Dainely Belt',
            'subtitle'         => 'Premium Lumbar Support',
            'image'            => asset('images/dainely-belt-product.png'),
            'price'            => 89.00,
            'compare_at_price' => 119.00,
            'quantity'         => 1,
            'option_label'     => null,
            'option_value'     => null,
            'variant_id'       => null,
            'source'           => 'static',
        ];
    }

    /**
     * Get all cart items. If empty, return a list containing the default item.
     * @return array<array<string, mixed>>
     */
    public static function get(): array
    {
        $items = session(self::SESSION_KEY, []);

        if (! is_array($items) || empty($items)) {
            // For backward compatibility / fallback, check the old key first
            $oldCart = session('checkout.cart');
            if (is_array($oldCart) && ! empty($oldCart['title'])) {
                return [array_merge(self::defaults(), $oldCart)];
            }
            return [self::defaults()];
        }

        return array_map(function ($item) {
            return array_merge(self::defaults(), $item);
        }, $items);
    }

    /**
     * Whether the shopper has any items saved in session.
     */
    public static function exists(): bool
    {
        $items = session(self::SESSION_KEY);
        if (is_array($items) && ! empty($items)) {
            return true;
        }
        return session()->has('checkout.cart');
    }

    /**
     * Add or update an item in the cart.
     * @param  array<string, mixed>  $item
     */
    public static function add(array $item): void
    {
        $items = session(self::SESSION_KEY, []);
        if (! is_array($items)) {
            $items = [];
        }

        // Check legacy single cart item and migrate if present
        $oldCart = session('checkout.cart');
        if (is_array($oldCart) && ! empty($oldCart['title'])) {
            $items[] = array_merge(self::defaults(), $oldCart);
            session()->forget('checkout.cart');
        }

        $item = array_merge(self::defaults(), $item);
        $found = false;

        foreach ($items as &$existingItem) {
            // Compare items by variant_id, or if variant_id is null, by product_id and option_value/option_label
            $sameVariant = ! empty($item['variant_id']) && $existingItem['variant_id'] === $item['variant_id'];
            $sameProductNoVariant = empty($item['variant_id']) && empty($existingItem['variant_id']) 
                && $existingItem['product_id'] === $item['product_id']
                && $existingItem['option_value'] === $item['option_value'];

            if ($sameVariant || $sameProductNoVariant) {
                $existingItem['quantity'] += $item['quantity'];
                $found = true;
                break;
            }
        }

        if (! $found) {
            $items[] = $item;
        }

        session([self::SESSION_KEY => $items]);
    }

    /**
     * Overwrite all items in the cart.
     * @param  array  $items
     */
    public static function put(array $items): void
    {
        // If it's a single item (backward compatibility), wrap it in an array or call add
        if (isset($items['title'])) {
            self::add($items);
            return;
        }

        session([self::SESSION_KEY => $items]);
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
        session()->forget('checkout.cart');
    }
}
