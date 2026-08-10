<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoLocaleService
{
    protected array $supported = ['en', 'fr', 'de'];

    public function detectLocaleFromRequest(Request $request): string
    {
        if (! config('geo.enabled', true)) {
            return $this->localeFromAcceptLanguage($request);
        }

        $country = $this->resolveCountryCode($request);

        if ($country !== null) {
            $mapped = $this->mapCountryToLocale($country);
            if ($mapped !== null) {
                return $mapped;
            }
        }

        return $this->localeFromAcceptLanguage($request);
    }

    public function resolveCountryCode(Request $request): ?string
    {
        if ($testCountry = $this->testCountryOverride()) {
            return $testCountry;
        }

        $cfCountry = strtoupper(trim((string) $request->header('CF-IPCountry', '')));
        if ($this->isValidCountryCode($cfCountry)) {
            return $cfCountry;
        }

        // Session / cookie already known — never block the request on IP APIs again.
        $sessionCountry = strtoupper(trim((string) $request->session()->get('geo_country', '')));
        if ($this->isValidCountryCode($sessionCountry)) {
            return $sessionCountry;
        }

        $cookieCountry = strtoupper(trim((string) $request->cookie('geo_country', '')));
        if ($this->isValidCountryCode($cookieCountry)) {
            return $cookieCountry;
        }

        $ip = $this->resolveClientIp($request);

        if ($this->isLocalIp($ip)) {
            return null;
        }

        return $this->lookupCountryCodeByIp($ip);
    }

    public function mapCountryToLocale(string $countryCode): ?string
    {
        $mapped = config('geo.country_locale.' . strtoupper($countryCode));

        return is_string($mapped) && in_array($mapped, $this->supported, true)
            ? $mapped
            : null;
    }

    public function mapCountryToCurrency(string $countryCode): ?string
    {
        $code = strtoupper(trim($countryCode));
        if ($code === '') {
            return null;
        }

        $explicit = config('geo.country_currency.' . $code);
        if (is_string($explicit) && $explicit !== '') {
            return strtoupper($explicit);
        }

        $eurozone = config('geo.eurozone_countries', []);
        if (is_array($eurozone) && in_array($code, $eurozone, true)) {
            return 'EUR';
        }

        return null;
    }

    /**
     * Full ISO country → currency map for client-side checkout updates.
     *
     * @return array<string, string>
     */
    public function countryCurrencyMap(): array
    {
        $map = [];
        foreach (config('geo.country_currency', []) as $country => $currency) {
            $map[strtoupper((string) $country)] = strtoupper((string) $currency);
        }
        foreach (config('geo.eurozone_countries', []) as $country) {
            $code = strtoupper((string) $country);
            $map[$code] = 'EUR';
        }

        return $map;
    }

    /**
     * Default shipping country on checkout (locale + geo session).
     *
     * @param  list<string>  $allowedCountries  ISO codes offered in the country select
     */
    public function defaultCheckoutCountry(string $locale, array $allowedCountries): string
    {
        if ($locale === 'fr') {
            return 'FR';
        }

        if ($locale === 'de') {
            return 'DE';
        }

        $allowed = array_map(static fn ($code) => strtoupper((string) $code), $allowedCountries);
        $geo = strtoupper(trim((string) session('geo_country', '')));

        if ($this->isValidCountryCode($geo) && in_array($geo, $allowed, true)) {
            return $geo;
        }

        return 'US';
    }

    public function detectCurrencyFromRequest(Request $request, ?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();

        if (in_array($locale, ['fr', 'de'], true)) {
            return (string) config('currency.locale_currency.' . $locale, 'EUR');
        }

        $country = $this->resolveCountryCode($request);
        if ($country !== null) {
            $mapped = $this->mapCountryToCurrency($country);
            if ($mapped !== null) {
                return $mapped;
            }
        }

        return (string) config('currency.locale_currency.en', 'USD');
    }

    protected function testCountryOverride(): ?string
    {
        if (! app()->environment(['local', 'testing'])) {
            return null;
        }

        $code = strtoupper(trim((string) config('geo.test_country', '')));

        return $this->isValidCountryCode($code) ? $code : null;
    }

    protected function isValidCountryCode(string $code): bool
    {
        return strlen($code) === 2
            && ctype_alpha($code)
            && ! in_array($code, ['XX', 'T1'], true);
    }

    protected function localeFromAcceptLanguage(Request $request): string
    {
        $browserLocale = substr((string) $request->header('Accept-Language', 'en'), 0, 2);

        return in_array($browserLocale, $this->supported, true)
            ? $browserLocale
            : (string) config('geo.default_locale', 'en');
    }

    protected function resolveClientIp(Request $request): string
    {
        foreach (['CF-Connecting-IP', 'True-Client-IP', 'X-Real-IP', 'X-Forwarded-For'] as $header) {
            $value = trim((string) $request->header($header, ''));
            if ($value === '') {
                continue;
            }

            $ip = trim(explode(',', $value)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return (string) ($request->ip() ?? '127.0.0.1');
    }

    protected function isLocalIp(string $ip): bool
    {
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'], true)) {
            return true;
        }

        return filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        ) === false;
    }

    protected function lookupCountryCodeByIp(string $ip): ?string
    {
        $cacheKey = 'geo_country_' . md5($ip);
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached === '__unknown__' ? null : $cached;
        }

        $country = $this->fetchFromIpApiComFree($ip)
            ?? $this->fetchFromIpApiCo($ip)
            ?? $this->fetchFromIpApiComKeyed($ip);

        Cache::put(
            $cacheKey,
            $country ?? '__unknown__',
            now()->addSeconds((int) config('geo.cache_ttl', 86400))
        );

        return $country;
    }

    protected function fetchFromIpApiComFree(string $ip): ?string
    {
        try {
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,countryCode',
            ]);

            if (! $response->successful() || $response->json('status') !== 'success') {
                return null;
            }

            $code = $response->json('countryCode');

            return is_string($code) && strlen($code) === 2 ? strtoupper($code) : null;
        } catch (\Throwable $e) {
            Log::debug('GeoLocale ip-api.com (free) failed: ' . $e->getMessage());

            return null;
        }
    }

    protected function fetchFromIpApiCo(string $ip): ?string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(['User-Agent' => 'Dainely-GeoLocale/1.0'])
                ->get("https://ipapi.co/{$ip}/country/");

            if (! $response->successful()) {
                return null;
            }

            $code = trim($response->body());

            return strlen($code) === 2 ? strtoupper($code) : null;
        } catch (\Throwable $e) {
            Log::debug('GeoLocale ipapi.co failed: ' . $e->getMessage());

            return null;
        }
    }

    protected function fetchFromIpApiComKeyed(string $ip): ?string
    {
        $key = trim((string) config('geo.ipapi_key', ''));
        if ($key === '') {
            return null;
        }

        try {
            $response = Http::timeout(5)->get("https://pro.ip-api.com/json/{$ip}", [
                'fields' => 'countryCode',
                'key'    => $key,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $code = $response->json('countryCode');

            return is_string($code) && strlen($code) === 2 ? strtoupper($code) : null;
        } catch (\Throwable $e) {
            Log::debug('GeoLocale ip-api.com (keyed) failed: ' . $e->getMessage());

            return null;
        }
    }
}
