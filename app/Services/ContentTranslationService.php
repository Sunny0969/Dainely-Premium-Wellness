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
 * Optimized for admin "Translate EN â†’ FR & DE": caches results, dedupes text
 * nodes, and runs MyMemory requests in parallel so the button does not hang
 * for minutes on rich HTML fields.
 */
class ContentTranslationService
{
    protected const CACHE_TTL_DAYS = 30;

    protected const POOL_SIZE = 6;

    /** Google answers one request per ~1.5k chars; longer text is split first. */
    protected const GOOGLE_CHUNK = 1500;

    /** Up to this many short snippets ride together, one response segment each. */
    protected const GOOGLE_BATCH_COUNT = 4;

    protected const GOOGLE_BATCH_CHARS = 2400;

    protected const GOOGLE_BATCH_LINE = 900;

    /** MyMemory free tier: ~1 request/second. Sequential + delayed, and capped. */
    protected const MYMEMORY_MAX_CALLS = 60;

    protected const MYMEMORY_DELAY_US = 350000;

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

        // Translate every unique snippet â†’ each target locale (cached + pooled).
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

        if (! in_array($to, ['fr', 'de', 'nl'], true)) {
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
                if (is_string($cached) && $cached !== '' && ! $this->looksUntranslated($cached, $snippet)) {
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

                    if (! $this->looksUntranslated($text, $snippet)) {
                        Cache::put($cacheKey, $text, now()->addDays(self::CACHE_TTL_DAYS));
                    }

                    $result[$snippet][$locale] = $text;
                } catch (Throwable $e) {
                    Log::warning('DeepL snippet failed', ['error' => $e->getMessage()]);
                    $result[$snippet][$locale] = $snippet;
                }
            }

            return $result;
        }

        // Free providers. Google first (fast, no key, tolerates parallel calls),
        // then MyMemory as a polite fallback — its free tier only allows about one
        // request per second, so it must stay sequential and capped or the whole
        // daily quota is burned by a single page and everything else stays English.
        $meta = [];        // index => [snippet, locale, cacheKey]
        $partResults = []; // "index\0partIndex" => translated text (null = failed)
        $jobs = [];        // list of [i, partIndex, part, locale]
        $jobsByIndex = []; // index => list of partIndex

        foreach ($pending as $index => [$snippet, $locale, $cacheKey]) {
            $meta[$index] = [$snippet, $locale, $cacheKey];

            foreach ($this->splitText($snippet, self::GOOGLE_CHUNK) as $partIndex => $part) {
                $jobs[] = ['i' => $index, 'partIndex' => $partIndex, 'part' => $part, 'locale' => $locale];
                $jobsByIndex[$index][] = $partIndex;
            }
        }

        if (! $this->googleDisabled()) {
            // Google returns exactly one response segment per input line, so a few
            // short snippets can ride together and be split back apart by segment
            // order — a whole page's worth of fields becomes a handful of requests
            // instead of fifty.
            $megaJobs = $this->batchGoogleJobs($jobs);

            $googleBlocked = false;

            foreach (array_chunk($megaJobs, self::POOL_SIZE) as $batch) {
                if ($googleBlocked) {
                    // Google is throttling this client — don't burn the rest of the
                    // batch on doomed calls; MyMemory below picks up the slack.
                    break;
                }

                $responses = Http::pool(function (Pool $pool) use ($batch) {
                    foreach ($batch as $i => $megaJob) {
                        $req = $pool->as((string) $i)
                            ->timeout(30)
                            ->connectTimeout(10)
                            ->withOptions($this->h2Options());

                        if ($this->shouldSkipSslVerify()) {
                            $req = $req->withoutVerifying();
                        }

                        $req->get('https://translate.googleapis.com/translate_a/single', [
                            'client' => 'gtx',
                            'sl' => 'en',
                            'tl' => $megaJob['locale'],
                            'dt' => 't',
                            'q' => implode("\n", $megaJob['texts']),
                        ]);
                    }
                });

                foreach ($batch as $i => $megaJob) {
                    $response = $responses[(string) $i] ?? null;

                    if (
                        is_object($response) && method_exists($response, 'status')
                        && in_array($response->status(), [403, 429], true)
                    ) {
                        $googleBlocked = true;
                        continue;
                    }

                    $segments = $this->parseGoogleSegments($response);

                    if ($segments === null) {
                        continue;
                    }

                    $texts = $megaJob['texts'];
                    $keys = $megaJob['keys'];
                    $translated = null;

                    if (count($segments) === count($texts)) {
                        $translated = array_map(fn ($s) => trim($s), $segments);
                    } else {
                        // Google splits a long line into several segments but keeps
                        // the newlines between input lines, so rejoining and
                        // re-splitting restores the original one-piece-per-snippet map.
                        $rejoined = array_map(fn ($p) => trim($p), explode("\n", implode('', $segments)));

                        if (count($rejoined) !== count($texts)) {
                            $rejoined = array_values(array_filter($rejoined, fn ($p) => $p !== ''));
                        }

                        if (count($rejoined) === count($texts)) {
                            $translated = $rejoined;
                        } elseif (count($texts) === 1) {
                            $translated = [trim(implode('', $segments))];
                        }
                    }

                    if ($translated === null) {
                        continue;
                    }

                    foreach ($translated as $k => $text) {
                        if (! isset($keys[$k])) {
                            break;
                        }

                        $partResults[$keys[$k]] = $text === '' ? null : $text;
                    }
                }
            }
        }

        $calls = 0;
        foreach ($jobs as $job) {
            $key = $job['i']."\0".$job['partIndex'];
            if (($partResults[$key] ?? null) !== null) {
                continue;
            }
            if ($calls >= self::MYMEMORY_MAX_CALLS) {
                break;
            }

            if ($calls > 0) {
                usleep(self::MYMEMORY_DELAY_US);
            }
            $calls++;

            $partResults[$key] = $this->viaMyMemory($job['part'], $job['locale']);
        }

        // Reassemble. Cache ONLY fully successful, actually-translated text —
        // caching a fallback would freeze English text as the "translation" for 30 days.
        $failedSnippets = 0;

        foreach ($meta as $index => [$snippet, $locale, $cacheKey]) {
            $parts = [];
            $failed = false;

            foreach ($jobsByIndex[$index] ?? [] as $partIndex) {
                $text = $partResults[$index."\0".$partIndex] ?? null;

                if ($text === null) {
                    $failed = true;
                    break;
                }

                $parts[$partIndex] = $text;
            }

            if ($failed || $parts === []) {
                // Leave it English and do NOT cache: the next sync retries it.
                $failedSnippets++;
                $result[$snippet][$locale] = $snippet;

                continue;
            }

            ksort($parts);
            $text = implode(' ', $parts);

            if (! $this->looksUntranslated($text, $snippet)) {
                Cache::put($cacheKey, $text, now()->addDays(self::CACHE_TTL_DAYS));
            }

            $result[$snippet][$locale] = $text;
        }

        if ($failedSnippets > 0) {
            Log::warning('Translation provider left some snippets in English — will retry.', [
                'failed' => $failedSnippets,
                'total' => count($meta),
            ]);
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
        if (preg_match('/^[\s\d\W]+$/u', $chunk) && ! preg_match('/\p{L}/u', $chunk)) {
            return false;
        }

        return true;
    }

    /**
     * Sentence-aware splitting so long prose can travel in provider-sized pieces.
     *
     * @return list<string>
     */
    protected function splitText(string $text, int $limit = 450): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        if (mb_strlen($text) <= $limit) {
            return [$text];
        }

        $parts = preg_split('/(?<=[.!?])\s+/u', $text) ?: [$text];
        $out = [];
        $buffer = '';

        foreach ($parts as $part) {
            if (mb_strlen($buffer.' '.$part) > $limit && $buffer !== '') {
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
            if (mb_strlen($piece) <= $limit) {
                $final[] = $piece;
                continue;
            }
            $len = mb_strlen($piece);
            for ($i = 0; $i < $len; $i += $limit) {
                $final[] = mb_substr($piece, $i, $limit);
            }
        }

        return $final;
    }

    protected function viaMyMemory(string $text, string $locale): ?string
    {
        $response = $this->http()->get('https://api.mymemory.translated.net/get', [
            'q' => $text,
            'langpair' => 'en|'.$locale,
            'de' => config('mail.from.address', 'admin@dainelylab.com'),
        ]);

        return $this->parseMyMemoryResponse($response);
    }

    /**
     * Pack short single-part snippets into shared requests (max count / length),
     * grouped per locale. Anything long or multiline travels on its own.
     *
     * @param  list<array{i: int, partIndex: int, part: string, locale: string}>  $jobs
     * @return list<array{locale: string, texts: list<string>, keys: list<string>, chars: int}>
     */
    protected function batchGoogleJobs(array $jobs): array
    {
        $megaJobs = [];
        $open = []; // locale => index into $megaJobs

        foreach ($jobs as $job) {
            $text = $job['part'];
            $key = $job['i']."\0".$job['partIndex'];
            $length = mb_strlen($text);

            $batchable = $job['partIndex'] === 0
                && $length <= self::GOOGLE_BATCH_LINE
                && ! str_contains($text, "\n");

            $slot = $batchable && isset($open[$job['locale']]) ? $open[$job['locale']] : null;

            if ($slot !== null) {
                $megaJobs[$slot]['texts'][] = $text;
                $megaJobs[$slot]['keys'][] = $key;
                $megaJobs[$slot]['chars'] += $length + 1;

                if (
                    count($megaJobs[$slot]['texts']) >= self::GOOGLE_BATCH_COUNT
                    || $megaJobs[$slot]['chars'] >= self::GOOGLE_BATCH_CHARS
                ) {
                    unset($open[$job['locale']]);
                }

                continue;
            }

            $megaJobs[] = [
                'locale' => $job['locale'],
                'texts' => [$text],
                'keys' => [$key],
                'chars' => $length,
            ];

            if ($batchable) {
                $open[$job['locale']] = count($megaJobs) - 1;
            }
        }

        return $megaJobs;
    }

    /**
     * @return list<string>|null null = provider failed / unusable payload
     */
    protected function parseGoogleSegments(mixed $response): ?array
    {
        try {
            if (! is_object($response) || ! method_exists($response, 'successful') || ! $response->successful()) {
                return null;
            }

            $json = $response->json();

            if (! is_array($json) || ! isset($json[0]) || ! is_array($json[0])) {
                return null;
            }

            $segments = [];
            foreach ($json[0] as $segment) {
                if (is_array($segment) && isset($segment[0]) && is_string($segment[0])) {
                    $segments[] = html_entity_decode($segment[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                }
            }

            return $segments === [] ? null : $segments;
        } catch (Throwable) {
            return null;
        }
    }

    protected function googleDisabled(): bool
    {
        return filter_var(env('TRANSLATE_DISABLE_GOOGLE', false), FILTER_VALIDATE_BOOLEAN);
    }

    protected function parseMyMemoryResponse(mixed $response): ?string
    {
        try {
            if (! is_object($response) || ! method_exists($response, 'successful') || ! $response->successful()) {
                return null;
            }

            $translated = $response->json('responseData.translatedText');
            $status = (int) ($response->json('responseStatus') ?? 0);

            if ($status !== 200 || ! is_string($translated) || $translated === '') {
                return null;
            }

            if (str_starts_with(strtoupper($translated), 'INVALID ')) {
                return null;
            }

            return html_entity_decode($translated, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Public for callers that persist translations (education sync, page blocks):
     * never write an "translation" that is still the English source.
     */
    public function isUntranslated(string $result, string $source): bool
    {
        return $this->looksUntranslated($result, $source);
    }

    /**
     * A prose snippet whose "translation" still equals the English source is a
     * poisoned entry left behind by a provider failure or rate limit.
     *
     * Two signals: a long run of identical text, or a full sentence of five or
     * more words coming back byte-identical.
     */
    protected function looksUntranslated(string $cached, string $source): bool
    {
        if ($cached !== $source) {
            return false;
        }

        $plain = trim(strip_tags($source));

        if ($plain === '' || preg_match('/\p{L}/u', $plain) !== 1) {
            return false;
        }

        if (mb_strlen($plain) > 40) {
            return true;
        }

        $words = preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return count($words) >= 5;
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

    /**
     * Guzzle's curl handler negotiates HTTP/1.1, and Google's translate endpoints
     * answer 429 Too Many Requests to plain HTTP/1.1 clients while serving HTTP/2
     * normally. Forcing HTTP/2-over-TLS (curl falls back to 1.1 when unavailable)
     * is the difference between every call failing and every call succeeding.
     */
    protected function h2Options(): array
    {
        return ['curl' => [CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2TLS]];
    }

    protected function http(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::timeout(30)->connectTimeout(10)->withOptions($this->h2Options());

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
