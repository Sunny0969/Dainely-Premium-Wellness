<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Simple site-wide settings (JSON file + cache).
 * Used for free-shipping threshold and similar storefront config.
 */
class SiteSettings
{
    public const CACHE_KEY = 'dainely.site_settings.v1';

    public const FREE_SHIPPING_THRESHOLD_USD = 'free_shipping_threshold_usd';

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 3600, function () {
            return self::readFile();
        });
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = self::all();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        $all = self::readFile();
        $all[$key] = $value;
        self::writeFile($all);
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function putMany(array $values): void
    {
        $all = array_merge(self::readFile(), $values);
        self::writeFile($all);
        Cache::forget(self::CACHE_KEY);
    }

    public static function freeShippingThresholdUsd(): float
    {
        $value = self::get(self::FREE_SHIPPING_THRESHOLD_USD, 29.99);
        $amount = is_numeric($value) ? (float) $value : 29.99;

        return max(0.0, round($amount, 2));
    }

    public static function setFreeShippingThresholdUsd(float $amount): void
    {
        self::set(self::FREE_SHIPPING_THRESHOLD_USD, max(0.0, round($amount, 2)));
    }

    /**
     * @return array<string, mixed>
     */
    protected static function readFile(): array
    {
        $path = self::path();
        if (! is_file($path)) {
            return self::defaults();
        }

        try {
            $raw = file_get_contents($path);
            $decoded = json_decode($raw ?: '{}', true);

            return is_array($decoded) ? array_merge(self::defaults(), $decoded) : self::defaults();
        } catch (\Throwable $e) {
            Log::warning('SiteSettings read failed', ['error' => $e->getMessage()]);

            return self::defaults();
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected static function writeFile(array $data): void
    {
        $path = self::path();
        $dir = dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents(
            $path,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    protected static function path(): string
    {
        return storage_path('app/site_settings.json');
    }

    /**
     * @return array<string, mixed>
     */
    protected static function defaults(): array
    {
        return [
            self::FREE_SHIPPING_THRESHOLD_USD => 29.99,
        ];
    }
}
