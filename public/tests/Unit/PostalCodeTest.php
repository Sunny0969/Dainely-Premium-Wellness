<?php

namespace Tests\Unit;

use App\Support\PostalCode;
use Tests\TestCase;

class PostalCodeTest extends TestCase
{
    public function test_uk_alphanumeric_postcodes(): void
    {
        $this->assertTrue(PostalCode::isValid('GB', 'SW1A 1AA'));
        $this->assertFalse(PostalCode::isValid('GB', 'Lahore'));
    }

    public function test_canada_and_netherlands_structured_formats(): void
    {
        $this->assertTrue(PostalCode::isValid('CA', 'K1A 0B1'));
        $this->assertTrue(PostalCode::isValid('NL', '1012 AB'));
        $this->assertTrue(PostalCode::isValid('IE', 'D02 X285'));
        $this->assertFalse(PostalCode::isValid('NL', 'Lahore'));
    }

    public function test_us_numeric_only(): void
    {
        $this->assertTrue(PostalCode::isValid('US', '10001'));
        $this->assertFalse(PostalCode::isValid('US', 'SW1A 1AA'));
        $this->assertFalse(PostalCode::isValid('US', 'Lahore'));
    }

    public function test_portugal_rejects_city_names(): void
    {
        $this->assertFalse(PostalCode::isValid('PT', 'Lahore'));
        $this->assertTrue(PostalCode::isValid('PT', '1000-001'));
        $this->assertTrue(PostalCode::isValid('PT', '1000001'));
    }

    public function test_france_and_germany_numeric_only(): void
    {
        $this->assertTrue(PostalCode::isValid('FR', '75001'));
        $this->assertTrue(PostalCode::isValid('DE', '10115'));
        $this->assertFalse(PostalCode::isValid('FR', 'Lahore'));
        $this->assertFalse(PostalCode::isValid('DE', 'Lahore'));
    }

    public function test_australia_four_digit_postcodes(): void
    {
        $this->assertTrue(PostalCode::isValid('AU', '2000'));
        $this->assertFalse(PostalCode::isValid('AU', '200'));
        $this->assertFalse(PostalCode::isValid('AU', 'SW1A 1AA'));
    }

    public function test_square_card_prefill_skips_au_nz_only(): void
    {
        $this->assertSame('10001', PostalCode::squareCardPrefill('US', '10001'));
        $this->assertSame('SW1A 1AA', PostalCode::squareCardPrefill('GB', 'sw1a 1aa'));
        $this->assertSame('10115', PostalCode::squareCardPrefill('DE', '10115'));
        $this->assertNull(PostalCode::squareCardPrefill('AU', '2000'));
        $this->assertNull(PostalCode::squareCardPrefill('NZ', '1010'));
        $this->assertNull(PostalCode::squareCardPrefill('US', '2000'));
    }
}
