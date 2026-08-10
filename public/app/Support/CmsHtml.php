<?php

namespace App\Support;

/**
 * Normalize admin rich-text HTML so paragraph spacing stays consistent on the storefront.
 */
class CmsHtml
{
    /**
     * Clean TinyMCE / pasted HTML for display (and optional save).
     */
    public static function normalize(?string $html): string
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
        $html = preg_replace('/<img\b[^>]*>/i', '', $html) ?? $html;
        $html = preg_replace('/<\/?picture\b[^>]*>/i', '', $html) ?? $html;

        // Drop pasted Word/TinyMCE font overrides so the storefront stays one typeface.
        $html = preg_replace('/\s*face\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html) ?? $html;
        $html = preg_replace_callback(
            '/\sstyle\s*=\s*("([^"]*)"|\'([^\']*)\')/i',
            static function (array $m): string {
                $quote = str_starts_with($m[1], '"') ? '"' : "'";
                $style = $m[2] !== '' ? $m[2] : $m[3];
                $parts = array_filter(array_map('trim', explode(';', (string) $style)), static function (string $decl): bool {
                    if ($decl === '') {
                        return false;
                    }
                    $prop = strtolower(trim(explode(':', $decl, 2)[0] ?? ''));

                    return ! in_array($prop, [
                        'font',
                        'font-family',
                        'font-size',
                        'font-style',
                        'font-variant',
                        'font-stretch',
                        'font-weight',
                        'line-height',
                        'letter-spacing',
                    ], true);
                });

                if ($parts === []) {
                    return '';
                }

                return ' style='.$quote.implode('; ', $parts).$quote;
            },
            $html
        ) ?? $html;

        // Plain text → real paragraphs (double newline = new paragraph).
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
            static fn (string $p): string => '<p>'.nl2br(e($p), false).'</p>',
            $parts
        ));
    }
}
