<?php

namespace App\Support;

/**
 * Normalize admin rich-text HTML so paragraph spacing stays consistent on the storefront.
 */
class CmsHtml
{
    /**
     * Clean TinyMCE / pasted HTML for display (and optional save).
     *
     * @param  bool  $keepTextStyles  Keep font-size / weight / style / line-height so a Word paste
     *                                keeps its hierarchy. The typeface is still forced back to the
     *                                site font so the storefront keeps one voice.
     */
    public static function normalize(?string $html, bool $keepTextStyles = false): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        // Recover content that was accidentally stored HTML-entity-encoded.
        if (str_contains($html, '&lt;') && ! preg_match('/<(?:p|ul|ol|li|strong|b|em|u|br|table|a)\b/i', $html)) {
            $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if (is_string($decoded) && $decoded !== '' && $decoded !== $html) {
                $html = trim($decoded);
            }
        }

        // Strip media that should never appear in product overlay fields.
// Image stripping removed
// Picture stripping removed

        // Drop pasted Word/TinyMCE font overrides so the storefront stays one typeface.
        $html = preg_replace('/\s*face\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace_callback(
            '/\sstyle\s*=\s*("([^"]*)"|\'([^\']*)\')/i',
            static function (array $m) use ($keepTextStyles): string {
                $quote = str_starts_with($m[1], '"') ? '"' : "'";
                $style = $m[2] !== '' ? $m[2] : $m[3];

                // Typeface is never ours to inherit — but size/weight may be when asked.
                $drop = [
                    'font',
                    'font-family',
                    'font-size',
                    'font-style',
                    'font-variant',
                    'font-stretch',
                    'font-weight',
                    'line-height',
                    'letter-spacing',
                ];
                if ($keepTextStyles) {
                    $drop = ['font', 'font-family'];
                }

                $parts = array_filter(array_map('trim', explode(';', (string) $style)), static function (string $decl) use ($drop): bool {
                    if ($decl === '') {
                        return false;
                    }
                    $prop = strtolower(trim(explode(':', $decl, 2)[0] ?? ''));

                    return ! in_array($prop, $drop, true);
                });

                if ($parts === []) {
                    return '';
                }

                return ' style='.$quote.implode('; ', $parts).$quote;
            },
            $html
        ) ?? $html;

        // Plain text Ã¢â€ â€™ real paragraphs (double newline = new paragraph).
        if ($html === strip_tags($html)) {
            return self::plainToParagraphs($html);
        }

        // Keep formatting tags TinyMCE uses; drop empty spacer paragraphs only.
        $html = preg_replace(
            '/<p(?:\s[^>]*)?>\s*(?:&nbsp;|\x{00A0}|<br\s*\/?>|\s)*\s*<\/p>/iu',
            '',
            $html
        ) ?? $html;

        // Split accidental "para1<br><br>para2" inside one <p> into separate paragraphs.
        $html = preg_replace_callback(
            '/<p(\s[^>]*)?>(.*?)<\/p>/is',
            static function (array $m): string {
                $attrs = $m[1] ?? '';
                $inner = $m[2] ?? '';
                // Don't split paragraphs that already contain lists/tables.
                if (preg_match('/<(?:ul|ol|table|li)\b/i', $inner)) {
                    return '<p'.$attrs.'>'.$inner.'</p>';
                }
                $chunks = preg_split('/(?:\s*<br\s*\/?>\s*){2,}/i', $inner) ?: [$inner];
                $chunks = array_values(array_filter(array_map('trim', $chunks), static fn ($c) => $c !== ''));
                if (count($chunks) <= 1) {
                    return '<p'.$attrs.'>'.$inner.'</p>';
                }

                return implode('', array_map(
                    static fn (string $c): string => '<p'.$attrs.'>'.$c.'</p>',
                    $chunks
                ));
            },
            $html
        ) ?? $html;

        // Collapse 3+ consecutive <br> to a single paragraph break marker, then leave at most one.
        $html = preg_replace('/(?:\s*<br\s*\/?>\s*){3,}/i', '<br><br>', $html) ?? $html;

        return trim($html);
    }

    /**
     * Escape plain text into balanced <p> tags.
     */
    protected static function plainToParagraphs(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $parts = preg_split("/\n{2,}/", $text) ?: [];
        $parts = array_values(array_filter(array_map('trim', $parts), static fn ($p) => $p !== ''));

        if ($parts === []) {
            return '';
        }

        return implode('', array_map(
            static fn (string $p) => '<p>'.nl2br(e($p), false).'</p>',
            $parts
        ));
    }

    /**
     * Force every inline text/background pair to reach WCAG AA (4.5:1) contrast.
     *
     * $darkBlock tells us the backdrop behind markup that declares no background of its own.
     * Runs after normalize() so pasted Word markup is corrected before it ever ships.
     */
    public static function ensureContrast(string $html, bool $darkBlock = false): string
    {
        if (trim($html) === '') {
            return '';
        }

        return preg_replace_callback(
            '/\sstyle\s*=\s*("([^"]*)"|\'([^\']*)\')/i',
            static function (array $m) use ($darkBlock): string {
                $quote = str_starts_with($m[1], '"') ? '"' : "'";
                $style = $m[2] !== '' ? $m[2] : $m[3];

                $decls = [];
                foreach (explode(';', (string) $style) as $decl) {
                    $decl = trim($decl);
                    if ($decl !== '') {
                        $decls[] = $decl;
                    }
                }
                if ($decls === []) {
                    return $m[0];
                }

                $ownBg = null;
                $hasColor = false;
                foreach ($decls as $decl) {
                    $pair = explode(':', $decl, 2);
                    if (count($pair) !== 2) {
                        continue;
                    }
                    $prop = strtolower(trim($pair[0]));
                    if (in_array($prop, ['background-color', 'background'], true) && $ownBg === null) {
                        $ownBg = self::parseColor(trim($pair[1]));
                    } elseif ($prop === 'color') {
                        $hasColor = true;
                    }
                }

                $bgLum = $ownBg !== null ? self::relativeLuminance($ownBg) : ($darkBlock ? 0.0 : 1.0);
                $darkBg = $bgLum < 0.4;
                $safe = $darkBg ? '#f8fafc' : '#0f172a';

                $out = [];
                $fixed = false;
                foreach ($decls as $decl) {
                    $pair = explode(':', $decl, 2);
                    if (count($pair) === 2 && strtolower(trim($pair[0])) === 'color') {
                        $fg = self::parseColor(trim($pair[1]));
                        if ($fg !== null && self::contrastRatio(self::relativeLuminance($fg), $bgLum) < 4.5) {
                            $decl = 'color: '.$safe;
                            $fixed = true;
                        }
                    }
                    $out[] = $decl;
                }

                if (! $hasColor && $darkBg) {
                    $out[] = 'color: '.$safe;
                    $fixed = true;
                }

                if (! $fixed) {
                    return $m[0];
                }

                return ' style='.$quote.implode('; ', $out).$quote;
            },
            $html
        ) ?? $html;
    }

    /**
     * Pull an #hex / rgb() / rgba() / named colour out of a declaration value.
     *
     * @return array{0:int,1:int,2:int}|null
     */
    protected static function parseColor(string $value): ?array
    {
        if (preg_match('/#([0-9a-f]{3,8})\b/i', $value, $m)) {
            $hex = strtolower($m[1]);
            if (strlen($hex) === 3) {
                $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
            }
            if (strlen($hex) === 8) {
                if (hexdec(substr($hex, 6, 2)) < 128) {
                    return null; // effectively transparent
                }
                $hex = substr($hex, 0, 6);
            }
            if (strlen($hex) !== 6) {
                return null;
            }

            return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        }

        if (preg_match('/rgba?\(\s*([\d.]+)[\s,]+([\d.]+)[\s,]+([\d.]+)\s*(?:,\s*([\d.]+)\s*)?\)/i', $value, $m)) {
            if (isset($m[4]) && $m[4] !== '' && (float) $m[4] < 0.5) {
                return null;
            }

            return [(int) round((float) $m[1]), (int) round((float) $m[2]), (int) round((float) $m[3])];
        }

        $named = [
            'white' => '#ffffff',
            'black' => '#000000',
            'transparent' => null,
        ];
        $name = strtolower(trim(preg_replace('/[^a-z]/i', '', $value) ?? $value));
        if (array_key_exists($name, $named)) {
            if ($named[$name] === null) {
                return null;
            }

            return self::parseColor($named[$name]);
        }

        return null;
    }

    /**
     * WCAG relative luminance of an RGB triple.
     *
     * @param  array{0:int,1:int,2:int}  $rgb
     */
    protected static function relativeLuminance(array $rgb): float
    {
        $channels = array_map(static function (int $c): float {
            $v = $c / 255;

            return $v <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    protected static function contrastRatio(float $fgLum, float $bgLum): float
    {
        $hi = max($fgLum, $bgLum);
        $lo = min($fgLum, $bgLum);

        return ($hi + 0.05) / ($lo + 0.05);
    }
}
