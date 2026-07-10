<?php

namespace App\Support;

class ProductLandingLang
{
    /** @var array<string, string> */
    private const FLAG_MAP = [
        'isDainelyBelt'     => 'products_belt',
        'isBallMassager'    => 'products_ball',
        'isNeckCloud'       => 'products_neck',
        'isBackPatches'     => 'products_patches',
        'isHeatedJacket'    => 'products_jacket',
        'isFootMassager'    => 'products_fm',
        'isKneeBrace'       => 'products_knee',
        'isDainelyMassager' => 'products_percussion',
        'isShoulderBrace'   => 'products_shoulder',
        'isNeckStretcher'   => 'products_neck_stretcher',
        'isBackStretcher'   => 'products_back_stretcher',
        'isRelaxaLeg'       => 'products_relaxaleg',
        'isTourmalineBelt'  => 'products_tourmaline',
        'isDmedeSystem'     => 'products_dmede',
        'isErgoCushion'     => 'products_cushion',
        'isMushroomCoffee'  => 'products_coffee',
    ];

    /**
     * Map product boolean flags to a short lang key (e.g. products_belt, products_fm).
     */
    public static function resolveLangKey(string $handle, array $flags): ?string
    {
        foreach (self::FLAG_MAP as $flag => $langKey) {
            if (! empty($flags[$flag])) {
                return $langKey;
            }
        }

        return null;
    }

    /**
     * Full Laravel translation prefix for __() calls.
     * products_fm uses its own file; others use product_landing.{key}.
     */
    public static function translationPrefix(?string $langKey): ?string
    {
        if ($langKey === null) {
            return null;
        }

        return $langKey === 'products_fm'
            ? 'products_fm'
            : "product_landing.{$langKey}";
    }

    /**
     * Return a nested translation list (benefits, faqs, etc.).
     * __() returns the key string for empty arrays — never use it in @foreach.
     */
    public static function landingList(string $prefix, string $field): array
    {
        $data = trans($prefix);

        if (! is_array($data)) {
            return [];
        }

        $items = $data[$field] ?? [];

        return is_array($items) ? $items : [];
    }
}
