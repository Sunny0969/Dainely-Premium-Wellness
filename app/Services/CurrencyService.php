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
            return $this->fetchRatesFromApi();
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
     * Format a USD amount for display in target currency.
     */
    public function format(float $amountUsd, string $targetCurrency): string
    {
        $amount   = $this->convert($amountUsd, $targetCurrency);
        $currency = $this->supported[$targetCurrency] ?? ['symbol' => '$', 'decimals' => 2];

        return $currency['symbol'] . number_format($amount, $currency['decimals']);
    }

    /**
     * Get the currency code for the current locale.
     */
    public function getCurrencyForLocale(string $locale): string
    {
        $map = config('currency.locale_currency', []);
        return $map[$locale] ?? 'USD';
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

        $response = Http::timeout(10)->get($apiUrl, [
            'app_id' => $appId,
            'base'   => 'USD',
        ]);

        if ($response->failed()) {
            Log::error('OpenExchangeRates API request failed: ' . $response->status());
            return $this->fallbackRates();
        }

        $data = $response->json();
        return $data['rates'] ?? $this->fallbackRates();
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
        ];
    }
}
