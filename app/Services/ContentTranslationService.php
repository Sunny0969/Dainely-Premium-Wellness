<?php

namespace App\Services;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Translates admin overlay content from English into FR / DE.
 * Prefers DeepL when DEEPL_API_KEY is set; otherwise MyMemory (free, rate-limited).
 *
 * Optimized for admin "Translate EN → FR & DE": caches results, dedupes text
 * nodes, and runs MyMemory requests in parallel so the button does not hang
 * for minutes on rich HTML fields.
 */
class ContentTranslationService
{
    protected const CACHE_TTL_DAYS = 30;

    protected const POOL_SIZE = 6;

    /**
     * @param  array<string, string|null>  $fields  keyed field => English HTML/text
     * @param  list<string>  $targets  e.g. ['fr','de']
     * @return array<string, array<string, string|null>>  locale => fields
     */
    public function translateFields(array $fields, array $targets = ['fr', 'de']): array
    {
        $targets = array_values(array_filter(
            $targets,
            fn ($locale) => in_array($locale, ['fr', 'de'], true)
        ));

        $out = [];
        foreach ($targets as $locale) {
            $out[$locale] = [];
        }

        // Collect unique English snippets across all fields (plain + HTML text nodes).
        $uniqueSnippets = [];
        $fieldPlans = [];

        foreach ($fields as $key => $value) {
            if (! is_string($value) || trim(strip_tags($value)) === '') {
                foreach ($targets as $locale) {
                    $out[$locale][$key] = null;
                }
                $fieldPlans[$key] = null;

                continue;
            }

            if ($value === strip_tags($value)) {
                $trimmed = trim($value);
                $uniqueSnippets[$trimmed] = $trimmed;
                $fieldPlans[$key] = ['type' => 'plain', 'text' => $trimmed];
            } else {
                $nodes = $this->extractTextNodes($value);
                foreach ($nodes as $node) {
                    $uniqueSnippets[$node] = $node;
                }
                $fieldPlans[$key] = ['type' => 'html', 'html' => $value, 'nodes' => $nodes];
            }
        }

        // Translate every unique snippet → each target locale (cached + pooled).
        $translations = $this->translateSnippetsToLocales(array_values($uniqueSnippets), $targets);

        foreach ($fieldPlans as $key => $plan) {
            if ($plan === null) {
                continue;
            }

            foreach ($targets as $locale) {
                try {
                    if ($plan['type'] === 'plain') {
                        $out[$locale][$key] = $translations[$plan['text']][$locale] ?? $plan['text'];
                    } else {
                        $map = [];
                        foreach ($plan['nodes'] as $node) {
                            $map[$node] = $translations[$node][$locale] ?? $node;
                        }
                        $out[$locale][$key] = $this->applyTextNodeMap($plan['html'], $map);
                    }
                } catch (Throwable $e) {
                    Log::warning('Content translation failed', [
                        'field' => $key,
                        'target' => $locale,
                        'error' => $e->getMessage(),
                    ]);
                    $out[$locale][$key] = null;
                }
            }
        }

        return $out;
    }

    public function translateContent(string $content, string $from, string $to): string
    {
        $from = strtolower($from);
        $to = strtolower($to);

        if ($from === $to) {
            return $content;
        }

        if (! in_array($to, ['fr', 'de'], true)) {
            throw new \InvalidArgumentException("Unsupported target locale: {$to}");
        }

        if ($this->deeplKey() !== '') {
            return $this->viaDeepL($content, $from, $to);
        }

        if ($content === strip_tags($content)) {
            $map = $this->translateSnippetsToLocales([trim($content)], [$to]);

            return $map[trim($content)][$to] ?? $content;
        }

        $nodes = $this->extractTextNodes($content);
        $map = [];
        $translated = $this->translateSnippetsToLocales($nodes, [$to]);
        foreach ($nodes as $node) {
            $map[$node] = $translated[$node][$to] ?? $node;
        }

        return $this->applyTextNodeMap($content, $map);
    }

    /**
     * @param  list<string>  $snippets
     * @param  list<string>  $targets
     * @return array<string, array<string, string>>  snippet => [locale => text]
     */
    protected function translateSnippetsToLocales(array $snippets, array $targets): array
    {
        $snippets = array_values(array_unique(array_filter(
            array_map('strval', $snippets),
            fn ($s) => $this->shouldTranslateChunk($s)
        )));

        $result = [];
        $pending = []; // list of [snippet, locale, cacheKey]

        foreach ($snippets as $snippet) {
            $result[$snippet] = [];
            foreach ($targets as $locale) {
                $cacheKey = $this->cacheKey($snippet, 'en', $locale);
                $cached = Cache::get($cacheKey);
                if (is_string($cached) && $cached !== '') {
                    $result[$snippet][$locale] = $cached;
                } else {
                    $pending[] = [$snippet, $locale, $cacheKey];
                }
            }
        }

        if ($pending === []) {
            return $result;
        }

        if ($this->deeplKey() !== '') {
            foreach ($pending as [$snippet, $locale, $cacheKey]) {
                try {
                    $text = $this->viaDeepL($snippet, 'en', $locale);
                    Cache::put($cacheKey, $text, now()->addDays(self::CACHE_TTL_DAYS));
                    $result[$snippet][$locale] = $text;
                } catch (Throwable $e) {
                    Log::warning('DeepL snippet failed', ['error' => $e->getMessage()]);
                    $result[$snippet][$locale] = $snippet;
                }
            }

            return $result;
        }

        // MyMemory: chunk long texts, then pool HTTP requests.
        $jobs = [];
        foreach ($pending as [$snippet, $locale, $cacheKey]) {
            foreach ($this->splitForMyMemory($snippet) as $partIndex => $part) {
                $jobs[] = [
                    'snippet' => $snippet,
                    'locale' => $locale,
                    'cacheKey' => $cacheKey,
                    'partIndex' => $partIndex,
                    'part' => $part,
                ];
            }
        }

        $partResults = []; // snippet|locale|partIndex => translated

        foreach (array_chunk($jobs, self::POOL_SIZE) as $batch) {
            $responses = Http::pool(function (Pool $pool) use ($batch) {
                foreach ($batch as $i => $job) {
                    $req = $pool->as((string) $i)
                        ->timeout(8)
                        ->connectTimeout(5);

                    if ($this->shouldSkipSslVerify()) {
                        $req = $req->withoutVerifying();
                    }

                    $req->get('https://api.mymemory.translated.net/get', [
                        'q' => $job['part'],
                        'langpair' => 'en|'.$job['locale'],
                        'de' => config('mail.from.address', 'admin@dainelylab.com'),
                    ]);
                }
            });

            foreach ($batch as $i => $job) {
                $response = $responses[(string) $i] ?? null;
                $key = $job['snippet']."\0".$job['locale']."\0".$job['partIndex'];
                $partResults[$key] = $this->parseMyMemoryResponse($response, $job['part']);
            }
        }

        // Reassemble multi-part snippets and cache.
        $grouped = [];
        foreach ($jobs as $job) {
            $gKey = $job['snippet']."\0".$job['locale'];
            $grouped[$gKey]['snippet'] = $job['snippet'];
            $grouped[$gKey]['locale'] = $job['locale'];
            $grouped[$gKey]['cacheKey'] = $job['cacheKey'];
            $grouped[$gKey]['parts'][$job['partIndex']] = $partResults[
                $job['snippet']."\0".$job['locale']."\0".$job['partIndex']
            ] ?? $job['part'];
        }

        foreach ($grouped as $group) {
            ksort($group['parts']);
            $text = implode(' ', $group['parts']);
            Cache::put($group['cacheKey'], $text, now()->addDays(self::CACHE_TTL_DAYS));
            $result[$group['snippet']][$group['locale']] = $text;
        }

        // Fill any missing with original.
        foreach ($snippets as $snippet) {
            foreach ($targets as $locale) {
                if (! isset($result[$snippet][$locale])) {
                    $result[$snippet][$locale] = $snippet;
                }
            }
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    protected function extractTextNodes(string $html): array
    {
        if (! preg_match_all('/>([^<]+)</u', $html, $matches)) {
            return [];
        }

        $nodes = [];
        foreach ($matches[1] as $chunk) {
            if ($this->shouldTranslateChunk($chunk)) {
                $nodes[$chunk] = $chunk;
            }
        }

        return array_values($nodes);
    }

    /**
     * @param  array<string, string>  $map
     */
    protected function applyTextNodeMap(string $html, array $map): string
    {
        return (string) preg_replace_callback(
            '/>([^<]+)</u',
            static function (array $m) use ($map) {
                $chunk = $m[1];
                if (! array_key_exists($chunk, $map)) {
                    return $m[0];
                }

                return '>'.$map[$chunk].'<';
            },
            $html
        );
    }

    protected function shouldTranslateChunk(string $chunk): bool
    {
        if (trim($chunk) === '') {
            return false;
        }

        // Skip pure whitespace / punctuation-only chunks.
        if (preg_match('/^[\s\d\W]+$/u', $chunk) && ! preg_match('/[A-Za-zÀ-ÿ]/u', $chunk)) {
            return false;
        }

        return true;
    }

    /**
     * @return list<string>
     */
    protected function splitForMyMemory(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        if (mb_strlen($text) <= 450) {
            return [$text];
        }

        $parts = preg_split('/(?<=[.!?])\s+/u', $text) ?: [$text];
        $out = [];
        $buffer = '';

        foreach ($parts as $part) {
            if (mb_strlen($buffer.' '.$part) > 450 && $buffer !== '') {
                $out[] = $buffer;
                $buffer = $part;
            } else {
                $buffer = $buffer === '' ? $part : $buffer.' '.$part;
            }
        }

        if ($buffer !== '') {
            $out[] = $buffer;
        }

        // Hard-split any remaining oversized piece.
        $final = [];
        foreach ($out as $piece) {
            if (mb_strlen($piece) <= 450) {
                $final[] = $piece;
                continue;
            }
            $len = mb_strlen($piece);
            for ($i = 0; $i < $len; $i += 450) {
                $final[] = mb_substr($piece, $i, 450);
            }
        }

        return $final;
    }

    protected function parseMyMemoryResponse(mixed $response, string $fallback): string
    {
        try {
            if (! is_object($response) || ! method_exists($response, 'successful') || ! $response->successful()) {
                return $fallback;
            }

            $translated = $response->json('responseData.translatedText');
            $status = (int) ($response->json('responseStatus') ?? 0);

            if ($status !== 200 || ! is_string($translated) || $translated === '') {
                return $fallback;
            }

            if (str_starts_with(strtoupper($translated), 'INVALID ')) {
                return $fallback;
            }

            return html_entity_decode($translated, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } catch (Throwable) {
            return $fallback;
        }
    }

    protected function cacheKey(string $text, string $from, string $to): string
    {
        return 'translate.v2.'.md5($from.'|'.$to.'|'.$text);
    }

    protected function deeplKey(): string
    {
        return trim((string) config('services.deepl.key', env('DEEPL_API_KEY', '')));
    }

    protected function viaDeepL(string $content, string $from, string $to): string
    {
        $endpoint = filter_var(env('DEEPL_API_FREE', true), FILTER_VALIDATE_BOOLEAN)
            ? 'https://api-free.deepl.com/v2/translate'
            : 'https://api.deepl.com/v2/translate';

        $isHtml = $content !== strip_tags($content);

        $response = $this->http()
            ->asForm()
            ->withHeaders(['Authorization' => 'DeepL-Auth-Key '.$this->deeplKey()])
            ->post($endpoint, [
                'text' => $content,
                'source_lang' => strtoupper($from),
                'target_lang' => strtoupper($to),
                'tag_handling' => $isHtml ? 'html' : 'xml',
                'preserve_formatting' => '1',
            ]);

        if (! $response->successful()) {
            throw new \RuntimeException('DeepL HTTP '.$response->status().': '.$response->body());
        }

        $text = $response->json('translations.0.text');

        if (! is_string($text) || $text === '') {
            throw new \RuntimeException('DeepL returned empty translation.');
        }

        return $text;
    }

    protected function http(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(8)->connectTimeout(5);

        if ($this->shouldSkipSslVerify()) {
            $client = $client->withoutVerifying();
        }

        return $client;
    }

    protected function shouldSkipSslVerify(): bool
    {
        if (filter_var(env('TRANSLATE_HTTP_VERIFY', true), FILTER_VALIDATE_BOOLEAN) === false) {
            return true;
        }

        if (PHP_OS_FAMILY === 'Windows'
            && app()->environment('local')
            && ! ini_get('curl.cainfo')
            && ! ini_get('openssl.cafile')) {
            return true;
        }

        return false;
    }
}
