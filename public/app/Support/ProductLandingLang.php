<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;

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
     * Shopify handle → landing flag used by resolveLangKey().
     *
     * @var array<string, string>
     */
    private const HANDLE_FLAG = [
        'dainely-belt'                         => 'isDainelyBelt',
        'dainely-comfort-belt'                 => 'isDainelyBelt',
        'dainely-belt-2-b'                     => 'isDainelyBelt',
        'dainely-belt-2-c'                     => 'isDainelyBelt',
        'dainely-ball-massager'                => 'isBallMassager',
        'dainely™-ball-massager'               => 'isBallMassager',
        'dainely-ball-massager-1'              => 'isBallMassager',
        'neck-pain'                            => 'isNeckCloud',
        'back-pain-relief-patches-20-pcs'      => 'isBackPatches',
        'dainely-unisex-heated-jacket'         => 'isHeatedJacket',
        'dainely-foot-massager'                => 'isFootMassager',
        'dainely™-foot-massager'               => 'isFootMassager',
        'brace'                                => 'isKneeBrace',
        'dainely-knee-brace'                   => 'isKneeBrace',
        'dainely-massager'                     => 'isDainelyMassager',
        'dainely™-massager'                    => 'isDainelyMassager',
        'shoulder-brace'                       => 'isShoulderBrace',
        'dainely-shoulder-brace'               => 'isShoulderBrace',
        'stretcher'                            => 'isNeckStretcher',
        'dainely-neck-stretcher'               => 'isNeckStretcher',
        'dainely™-orthopedic-back-stretcher'   => 'isBackStretcher',
        'dainely-orthopedic-back-stretcher'    => 'isBackStretcher',
        'back-stretcher'                       => 'isBackStretcher',
        'leg-massager'                         => 'isRelaxaLeg',
        'relaxaleg-system'                     => 'isRelaxaLeg',
        'dainely-relaxaleg-system'             => 'isRelaxaLeg',
        'relaxaleg'                            => 'isRelaxaLeg',
        'dainely™-tourmaline-belt'            => 'isTourmalineBelt',
        'dainely-tourmaline-belt'             => 'isTourmalineBelt',
        'tourmaline-belt'                      => 'isTourmalineBelt',
        'dainely-daily-comfort-system'         => 'isDmedeSystem',
        'daily-relief-system'                  => 'isDmedeSystem',
        'dmede-daily-support'                  => 'isDmedeSystem',
        'dmede-daily-support-recovery-system'  => 'isDmedeSystem',
        'cushion'                              => 'isErgoCushion',
        'dainely-cushion'                      => 'isErgoCushion',
        'ergocushion'                          => 'isErgoCushion',
        'functional-mushroom-coffee'           => 'isMushroomCoffee',
        'mushroom-coffee'                      => 'isMushroomCoffee',
        'coffee'                               => 'isMushroomCoffee',
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

        return self::langKeyForHandle($handle);
    }

    public static function langKeyForHandle(string $handle): ?string
    {
        $flag = self::HANDLE_FLAG[$handle] ?? null;
        if ($flag === null) {
            return null;
        }

        return self::FLAG_MAP[$flag] ?? null;
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

    public static function translationPrefixForHandle(string $handle): ?string
    {
        return self::translationPrefix(self::langKeyForHandle($handle));
    }

    /**
     * Locale-aware landing string. If FR/DE file still has the English copy,
     * auto-translate once and cache (so header language switches update PDP copy).
     *
     * @param  array<string, scalar|null>  $replace
     */
    public static function line(string $prefix, string $key, array $replace = [], ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        if (! in_array($locale, ['en', 'fr', 'de'], true)) {
            $locale = 'en';
        }

        $fullKey = "{$prefix}.{$key}";
        $value = Lang::get($fullKey, [], $locale);
        if (! is_string($value) || $value === $fullKey) {
            $value = Lang::get($fullKey, [], 'en');
        }
        if (! is_string($value)) {
            return $fullKey;
        }

        if ($locale !== 'en') {
            $english = Lang::get($fullKey, [], 'en');
            if (is_string($english) && $english !== '' && $value === $english) {
                $value = self::autoTranslateCached($fullKey, $english, $locale);
            }
        }

        foreach ($replace as $name => $replacement) {
            $value = str_replace(':'.$name, (string) $replacement, $value);
        }

        return $value;
    }

    /**
     * Return a nested translation list (benefits, faqs, etc.).
     * Auto-translates leaf strings that are still English copies.
     *
     * @return array<int|string, mixed>
     */
    public static function landingList(string $prefix, string $field, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $data = Lang::get($prefix, [], $locale);
        if (! is_array($data)) {
            return [];
        }

        $items = $data[$field] ?? [];
        if (! is_array($items)) {
            return [];
        }

        if ($locale === 'en') {
            return $items;
        }

        $enData = Lang::get($prefix, [], 'en');
        $enItems = is_array($enData) ? ($enData[$field] ?? []) : [];
        if (! is_array($enItems)) {
            return $items;
        }

        return self::localizeNested($items, $enItems, "{$prefix}.{$field}", $locale);
    }

    /**
     * Default FAQs from lang files for a product handle + locale.
     *
     * @return list<array{question:string,answer:string}>
     */
    public static function defaultFaqsForHandle(string $handle, string $locale = 'en'): array
    {
        $prefix = self::translationPrefixForHandle($handle);
        if ($prefix === null) {
            return [];
        }

        $data = Lang::get($prefix, [], $locale);
        if (! is_array($data)) {
            return [];
        }

        $rows = $data['faqs'] ?? [];
        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $question = trim((string) ($row[1] ?? ''));
            $answer = trim((string) ($row[2] ?? ''));
            if ($question === '' || $answer === '') {
                continue;
            }
            $out[] = [
                'question' => $question,
                'answer'   => $answer,
            ];
        }

        return $out;
    }

    /**
     * @param  array<int|string, mixed>  $localeNode
     * @param  array<int|string, mixed>  $enNode
     * @return array<int|string, mixed>
     */
    protected static function localizeNested(array $localeNode, array $enNode, string $cachePrefix, string $locale): array
    {
        $out = [];
        foreach ($localeNode as $key => $value) {
            $enValue = $enNode[$key] ?? null;
            if (is_array($value) && is_array($enValue)) {
                $out[$key] = self::localizeNested($value, $enValue, "{$cachePrefix}.{$key}", $locale);
            } elseif (is_string($value) && is_string($enValue) && $value !== '' && $value === $enValue) {
                $out[$key] = self::autoTranslateCached("{$cachePrefix}.{$key}", $enValue, $locale);
            } else {
                $out[$key] = $value;
            }
        }

        return $out;
    }

    protected static function autoTranslateCached(string $cacheKey, string $english, string $locale): string
    {
        $english = trim($english);
        if ($english === '' || mb_strlen($english) < 2) {
            return $english;
        }

        // Skip pure placeholders / brand-only tokens.
        if (preg_match('/^[:\d\s™®]+$/u', $english)) {
            return $english;
        }

        $key = 'landing.copy.v2.'.md5($cacheKey.'|'.$locale.'|'.$english);
        $cached = \Illuminate\Support\Facades\Cache::get($key);
        if (is_string($cached) && $cached !== '' && $cached !== $english) {
            return $cached;
        }

        try {
            $translated = app(\App\Services\ContentTranslationService::class)
                ->translateContent($english, 'en', $locale);
            if (is_string($translated) && trim($translated) !== '' && $translated !== $english) {
                \Illuminate\Support\Facades\Cache::put($key, $translated, now()->addDays(30));

                return $translated;
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Landing copy auto-translate failed', [
                'key' => $cacheKey,
                'locale' => $locale,
                'error' => $e->getMessage(),
            ]);
        }

        return $english;
    }
}
