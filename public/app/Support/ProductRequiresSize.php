<?php



namespace App\Support;



use Illuminate\Support\Str;



class ProductRequiresSize

{

    public static function check(string $productId, string $title, ?string $handle = null): bool

    {

        $handle = strtolower(trim((string) $handle));

        if ($handle !== '') {

            foreach (config('products.requires_size.handles', []) as $configured) {

                if ($handle === strtolower($configured)) {

                    return true;

                }

            }

        }



        $id = strtolower(trim($productId));

        foreach (config('products.requires_size.handles', []) as $configured) {

            if ($id === strtolower($configured)) {

                return true;

            }

        }



        $needle = self::normalizeTitle($title);

        foreach (config('products.requires_size.title_patterns', []) as $pattern) {

            if ($needle !== '' && str_contains($needle, self::normalizeTitle($pattern))) {

                return true;

            }

        }



        return self::matchesLocalizedBeltTitle($needle);

    }



    public static function missingSelection(

        string $productId,

        string $title,

        ?string $optionLabel,

        ?string $optionValue,

        ?string $handle = null,

    ): bool {

        if (! self::check($productId, $title, $handle)) {

            return false;

        }



        $label = trim((string) $optionLabel);

        $value = trim((string) $optionValue);



        return $label === '' && $value === '';

    }



    private static function normalizeTitle(string $title): string

    {

        return strtolower(trim(Str::ascii($title)));

    }



    /**

     * Match FR/DE (and other) belt titles that do not contain the English word "belt".

     */

    private static function matchesLocalizedBeltTitle(string $normalizedTitle): bool

    {

        if ($normalizedTitle === '' || ! str_contains($normalizedTitle, 'dainely')) {

            return false;

        }



        foreach (['belt', 'ceinture', 'gurtel', 'guertel'] as $keyword) {

            if (str_contains($normalizedTitle, $keyword)) {

                return true;

            }

        }



        return false;

    }

}


