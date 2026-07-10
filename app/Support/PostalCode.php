<?php

namespace App\Support;

class PostalCode
{
    public static function placeholder(string $countryCode): string
    {
        $code = strtoupper(trim($countryCode));

        return (string) (config('postal.placeholders')[$code] ?? '10001');
    }

    public static function invalidMessage(string $countryCode): string
    {
        return __('checkout.err_zip_invalid', [
            'example' => self::placeholder($countryCode),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function patternsForClient(): array
    {
        return array_merge(
            config('postal.patterns', []),
            ['default' => '^\d[\d\s\-]{2,10}$'],
        );
    }

    public static function isValid(string $countryCode, string $postal): bool
    {
        $postal = trim($postal);
        if ($postal === '') {
            return false;
        }

        $code = strtoupper(trim($countryCode));
        $normalized = self::normalize($postal, $code);
        $pattern = config("postal.patterns.{$code}");

        if (is_string($pattern) && $pattern !== '') {
            return (bool) preg_match('/'.$pattern.'/i', $normalized);
        }

        // Unknown country: digits, spaces, hyphens only — must include at least one digit
        return (bool) preg_match('/^\d[\d\s\-]{2,10}$/', $normalized);
    }

    public static function normalize(string $postal, string $countryCode): string
    {
        $code = strtoupper(trim($countryCode));
        $upper = config('postal.uppercase_on_validate', ['GB', 'UK', 'CA', 'IE', 'NL']);

        if (in_array($code, $upper, true)) {
            return strtoupper($postal);
        }

        return $postal;
    }

    /**
     * Postal value safe to pass into Square CardOptions.postalCode for the given country.
     * Returns null when pre-fill would trigger incorrect US ZIP validation (e.g. AU, NZ).
     */
    public static function squareCardPrefill(string $countryCode, string $postal): ?string
    {
        $code = strtoupper(trim($countryCode));
        $skip = config('postal.square_skip_prefill_countries', ['AU', 'NZ']);

        if (in_array($code, $skip, true)) {
            return null;
        }

        if (! self::isValid($code, $postal)) {
            return null;
        }

        return self::normalize($postal, $code);
    }
}
