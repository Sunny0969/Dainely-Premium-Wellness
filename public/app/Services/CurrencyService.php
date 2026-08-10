<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CurrencyService
{
    protected string $baseCurrency;
    protected array $supported;
    protected int $cacheTtl;

    public function __construct()
    {
        $this->baseCurrency = config('currency.base_currency', 'USD');
        $this->supported    = config('currency.supported', []);
        $this->cacheTtl     = config('currency.open_exchange_rates.cache_ttl', 3600);
    }

    /**
     * Get exchange rates, from Redis cache or API.
     */
    public function getRates(): array
    {
        return Cache::remember('currency_rates', $this->cacheTtl, function () {
            return $this->mergeSupportedRates($this->fetchRatesFromApi());
        });
    }

    /**
     * Convert an amount from USD to the given currency.
     */
    public function convert(float $amountUsd, string $targetCurrency): float
    {
        if ($targetCurrency === 'USD') {
            return round($amountUsd, 2);
        }

        $rates = $this->getRates();
        $rate  = $rates[$targetCurrency] ?? 1.0;

        return round($amountUsd * $rate, 2);
    }

    /**
     * USD → target currency rate (e.g. 0.92 for EUR).
     */
    public function getUsdToCurrencyRate(string $targetCurrency): float
    {
        if ($targetCurrency === 'USD') {
            return 1.0;
        }

        $rates = $this->getRates();

        return (float) ($rates[$targetCurrency] ?? 1.0);
    }

    /**
     * Convert a display-currency amount back to USD (inverse of convert()).
     */
    public function convertToUsd(float $amountDisplay, string $displayCurrency): float
    {
        if ($displayCurrency === 'USD') {
            return round($amountDisplay, 2);
        }

        $rate = $this->getUsdToCurrencyRate($displayCurrency);
        if ($rate <= 0) {
            return round($amountDisplay, 2);
        }

        return round($amountDisplay / $rate, 2);
    }

    /**
     * Format a USD amount for display in target currency.
     */
    public function format(float $amountUsd, string $localeOrCurrency): string
    {
        $targetCurrency = strlen($localeOrCurrency) === 2 
            ? $this->getCurrencyForLocale($localeOrCurrency) 
            : $localeOrCurrency;

        $amount   = $this->convert($amountUsd, $targetCurrency);
        $currency = $this->supported[$targetCurrency] ?? ['symbol' => '$', 'decimals' => 2];

        return $currency['symbol'] . number_format($amount, $currency['decimals']);
    }

    /**
     * Get the display currency for the current locale and geo context.
     */
    public function getCurrencyForLocale(string $locale): string
    {
        return $this->resolveDisplayCurrency($locale);
    }

    /**
     * Resolve storefront display currency (locale + optional geo country).
     */
    public function resolveDisplayCurrency(string $locale, ?string $countryCode = null): string
    {
        if (in_array($locale, ['fr', 'de'], true)) {
            return (string) (config('currency.locale_currency')[$locale] ?? 'EUR');
        }

        $country = $countryCode ?? session('geo_country');
        if (is_string($country) && $country !== '') {
            $geo = app(GeoLocaleService::class);
            $mapped = $geo->mapCountryToCurrency($country);
            if ($mapped !== null && array_key_exists($mapped, $this->supported)) {
                return $mapped;
            }
        }

        return (string) (config('currency.locale_currency')['en'] ?? 'USD');
    }

    /**
     * Format a USD amount for display in the locale's currency.
     */
    public function formatForLocale(float $amountUsd, string $locale): string
    {
        return $this->format($amountUsd, $this->resolveDisplayCurrency($locale));
    }

    /**
     * @return array{code: string, symbol: string, name: string, decimals: int}
     */
    public function getCurrencyMeta(string $currencyCode): array
    {
        $meta = $this->supported[$currencyCode] ?? $this->supported['USD'];

        return [
            'code'     => $currencyCode,
            'symbol'   => $meta['symbol'] ?? '$',
            'name'     => $meta['name'] ?? $currencyCode,
            'decimals' => (int) ($meta['decimals'] ?? 2),
        ];
    }

    public function freeShippingThresholdUsd(): float
    {
        return \App\Support\SiteSettings::freeShippingThresholdUsd();
    }

    /**
     * Force-refresh the cached rates from the API.
     */
    public function refreshRates(): bool
    {
        Cache::forget('currency_rates');
        try {
            $rates = $this->fetchRatesFromApi();
            Cache::put('currency_rates', $rates, $this->cacheTtl);

            return true;
        } catch (\Exception $e) {
            Log::error('Currency rate refresh failed: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Fetch live rates from OpenExchangeRates API.
     */
    protected function fetchRatesFromApi(): array
    {
        $appId  = config('currency.open_exchange_rates.app_id');
        $apiUrl = config('currency.open_exchange_rates.api_url');

        if (empty($appId)) {
            Log::warning('OpenExchangeRates APP_ID not configured. Using fallback rates.');
            return $this->fallbackRates();
        }

        $response = Http::timeout(3)->connectTimeout(2)->get($apiUrl, [
            'app_id' => $appId,
            'base'   => 'USD',
        ]);

        if ($response->failed()) {
            Log::error('OpenExchangeRates API request failed: ' . $response->status());
            return $this->fallbackRates();
        }

        $data = $response->json();
        return $this->mergeSupportedRates($data['rates'] ?? $this->fallbackRates());
    }

    /**
     * Ensure every supported currency has a rate (API may omit or cache may be partial).
     *
     * @param  array<string, float|int>  $rates
     * @return array<string, float|int>
     */
    protected function mergeSupportedRates(array $rates): array
    {
        return array_merge($this->fallbackRates(), $rates);
    }

    /**
     * Hardcoded fallback rates if API is unavailable.
     */
    protected function fallbackRates(): array
    {
        return [
            'USD' => 1.0,
            'EUR' => 0.92,
            'GBP' => 0.79,
            'CAD' => 1.36,
            'AUD' => 1.53,
            'NZD' => 1.64,
            'SEK' => 10.5,
            'NOK' => 10.8,
            'DKK' => 6.9,
            'PLN' => 4.0,
            'ZAR' => 18.5,
        ];
    }
}
