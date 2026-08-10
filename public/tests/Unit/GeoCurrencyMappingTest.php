<?php

namespace Tests\Unit;

use App\Services\GeoLocaleService;
use Tests\TestCase;

class GeoCurrencyMappingTest extends TestCase
{
    public function test_eurozone_countries_map_to_eur(): void
    {
        $geo = app(GeoLocaleService::class);

        foreach (['NL', 'ES', 'GR', 'IT', 'PT', 'IE', 'BE', 'CH'] as $country) {
            $this->assertSame('EUR', $geo->mapCountryToCurrency($country), "Failed for {$country}");
        }
    }

    public function test_uk_maps_to_gbp(): void
    {
        $geo = app(GeoLocaleService::class);

        $this->assertSame('GBP', $geo->mapCountryToCurrency('GB'));
    }

    public function test_unmapped_country_returns_null(): void
    {
        $geo = app(GeoLocaleService::class);

        $this->assertNull($geo->mapCountryToCurrency('JP'));
        $this->assertSame('CAD', $geo->mapCountryToCurrency('CA'));
        $this->assertSame('AUD', $geo->mapCountryToCurrency('AU'));
        $this->assertSame('SEK', $geo->mapCountryToCurrency('SE'));
        $this->assertSame('ZAR', $geo->mapCountryToCurrency('ZA'));
    }

    public function test_default_checkout_country_uses_geo_session_for_en(): void
    {
        $geo = app(GeoLocaleService::class);
        $allowed = ['US', 'GB', 'ZA', 'PL'];

        session(['geo_country' => 'ZA']);
        $this->assertSame('ZA', $geo->defaultCheckoutCountry('en', $allowed));

        session(['geo_country' => 'PL']);
        $this->assertSame('PL', $geo->defaultCheckoutCountry('en', $allowed));

        session()->forget('geo_country');
        $this->assertSame('US', $geo->defaultCheckoutCountry('en', $allowed));
    }

    public function test_default_checkout_country_respects_regional_locales(): void
    {
        $geo = app(GeoLocaleService::class);
        $allowed = ['US', 'FR', 'DE', 'ZA'];

        session(['geo_country' => 'ZA']);
        $this->assertSame('FR', $geo->defaultCheckoutCountry('fr', $allowed));
        $this->assertSame('DE', $geo->defaultCheckoutCountry('de', $allowed));
    }
}
