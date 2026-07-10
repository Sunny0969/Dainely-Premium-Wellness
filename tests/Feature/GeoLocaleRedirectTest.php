<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeoLocaleRedirectTest extends TestCase
{
    public function test_root_redirects_to_french_when_cloudflare_country_is_france(): void
    {
        $response = $this->withHeaders(['CF-IPCountry' => 'FR'])->get('/');

        $response->assertRedirect(route('home', ['locale' => 'fr']));
    }

    public function test_root_redirects_to_german_when_cloudflare_country_is_germany(): void
    {
        $response = $this->withHeaders(['CF-IPCountry' => 'DE'])->get('/');

        $response->assertRedirect(route('home', ['locale' => 'de']));
    }

    public function test_root_respects_existing_locale_cookie(): void
    {
        $response = $this
            ->withHeaders(['CF-IPCountry' => 'FR'])
            ->withCookie('locale', 'en')
            ->get('/');

        $response->assertRedirect(route('home', ['locale' => 'en']));
    }

    public function test_english_home_redirects_to_french_for_french_ip_on_first_visit(): void
    {
        $response = $this->withHeaders(['CF-IPCountry' => 'FR'])->get('/en');

        $response->assertRedirect(route('home', ['locale' => 'fr']));
    }

    public function test_english_home_stays_english_for_us_ip(): void
    {
        $response = $this->withHeaders(['CF-IPCountry' => 'US'])->get('/en');

        $response->assertOk();
    }

    public function test_netherlands_ip_shows_eur_on_english_site(): void
    {
        $response = $this->withHeaders(['CF-IPCountry' => 'NL'])->get('/en');

        $response->assertOk();
        $response->assertSee('code: "EUR"', false);
    }

    public function test_spain_ip_shows_eur_on_english_site(): void
    {
        $response = $this->withHeaders(['CF-IPCountry' => 'ES'])->get('/en');

        $response->assertOk();
        $response->assertSee('code: "EUR"', false);
    }

    public function test_greece_ip_shows_eur_on_english_site(): void
    {
        $response = $this->withHeaders(['CF-IPCountry' => 'GR'])->get('/en');

        $response->assertOk();
        $response->assertSee('code: "EUR"', false);
    }

    public function test_uk_ip_shows_gbp_on_english_site(): void
    {
        $response = $this->withHeaders(['CF-IPCountry' => 'GB'])->get('/en');

        $response->assertOk();
        $response->assertSee('code: "GBP"', false);
    }

    public function test_us_ip_shows_usd_on_english_site(): void
    {
        $response = $this->withHeaders(['CF-IPCountry' => 'US'])->get('/en');

        $response->assertOk();
        $response->assertSee('code: "USD"', false);
    }
}
