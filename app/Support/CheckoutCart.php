<?php

namespace App\Support;

class CheckoutCart
{
    public const SESSION_KEY = 'checkout.cart';

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
     * @return array<string, mixed>
     */
    public static function get(): array
    {
        $cart = session(self::SESSION_KEY);

        if (! is_array($cart) || empty($cart['title'])) {
            return self::defaults();
        }

        return array_merge(self::defaults(), $cart);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public static function put(array $item): void
    {
        session([self::SESSION_KEY => array_merge(self::defaults(), $item)]);
    }

    public static function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
