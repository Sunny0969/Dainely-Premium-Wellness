<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Admin writes page blocks in English only.
 * Storefront auto-translates title/content for FR / DE (cached).
 * On any failure, English copy is still shown — blocks must never disappear.
 */
class PageBlockLocalizationService
{
    public function __construct(
        protected ContentTranslationService $translator
    ) {}

    /**
     * @param  Collection<int, object|array>  $englishBlocks
     * @return Collection<int, object>
     */
    public function forLocale(Collection $englishBlocks, string $locale): Collection
    {
        $locale = in_array($locale, ['en', 'fr', 'de'], true) ? $locale : 'en';

        $normalized = $englishBlocks->map(fn ($block) => $this->normalize($block))->values();

        if ($locale === 'en' || $normalized->isEmpty()) {
            return $normalized;
        }

        return $normalized->map(function ($block) use ($locale) {
            $title = trim((string) ($block->title ?? ''));
            $content = trim((string) ($block->content ?? ''));
            $id = (int) ($block->id ?? 0);
            $fingerprint = md5($title.'|'.$content);
            $cacheKey = "storefront.page_block.v2.{$id}.{$locale}.{$fingerprint}";

            $cached = Cache::get($cacheKey);
            if (is_array($cached) && isset($cached['title'], $cached['content'])) {
                return $this->withCopy($block, (string) $cached['title'], (string) $cached['content']);
            }

            $translatedTitle = $title;
            $translatedContent = $content;

            try {
                if ($title !== '') {
                    $translatedTitle = $this->translator->translateContent($title, 'en', $locale);
                }
                if ($content !== '') {
                    $translatedContent = $this->translator->translateContent($content, 'en', $locale);
                }

                // Never cache empty results that would blank the storefront section.
                if (trim(strip_tags($translatedTitle.$translatedContent)) === ''
                    && trim(strip_tags($title.$content)) !== '') {
                    $translatedTitle = $title;
                    $translatedContent = $content;
                } else {
                    Cache::put($cacheKey, [
                        'title'   => $translatedTitle,
                        'content' => $translatedContent,
                    ], now()->addDays(30));
                }
            } catch (Throwable $e) {
                Log::warning('Page block auto-translate failed', [
                    'block_id' => $id,
                    'locale'   => $locale,
                    'error'    => $e->getMessage(),
                ]);
                // Keep English — section stays visible.
                $translatedTitle = $title;
                $translatedContent = $content;
            }

            return $this->withCopy($block, $translatedTitle, $translatedContent);
        })->values();
    }

    /**
     * @param  object|array  $block
     */
    protected function normalize(object|array $block): object
    {
        if (is_array($block)) {
            $block = (object) $block;
        }

        return (object) [
            'id'         => (int) ($block->id ?? 0),
            'locale'     => (string) ($block->locale ?? 'en'),
            'block_type' => (string) ($block->block_type ?? ''),
            'title'      => (string) ($block->title ?? ''),
            'content'    => (string) ($block->content ?? ''),
            'sort_order' => (int) ($block->sort_order ?? 0),
            'visible'    => (bool) ($block->visible ?? true),
        ];
    }

    protected function withCopy(object $block, string $title, string $content): object
    {
        $copy = clone $block;
        $copy->title = $title;
        $copy->content = $content;

        return $copy;
    }
}
