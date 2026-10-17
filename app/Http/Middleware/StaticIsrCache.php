<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class StaticIsrCache
{
    /**
     * How long a full-page HTML snapshot stays cached (minutes).
     */
    public const TTL_MINUTES = 60;

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') || count($request->query()) > 0) {
            return $next($request);
        }

        if ($request->is('dainely-admin-panel', 'dainely-admin-panel/*')) {
            return $next($request);
        }

        $key = 'isr_cache_' . md5($request->fullUrl());
        $cachedResponse = Cache::get($key);

        if ($cachedResponse) {
            $content = $cachedResponse['content'];
            $currentToken = csrf_token();
            $content = preg_replace('/<meta\s+name="csrf-token"\s+content="[^"]+">/i', '<meta name="csrf-token" content="' . $currentToken . '">', $content);
            $content = preg_replace('/<input\s+type="hidden"\s+name="_token"\s+value="[^"]+"[^>]*>/i', '<input type="hidden" name="_token" value="' . $currentToken . '" autocomplete="off">', $content);
            return response($content)->withHeaders($cachedResponse['headers'])->header('X-Laravel-ISR', 'HIT');
        }

        /** @var Response $response */
        $response = $next($request);

        if ($response->getStatusCode() === 200 && str_contains($response->headers->get('Content-Type', ''), 'text/html')) {
            Cache::put($key, [
                'content' => $response->getContent(),
                'headers' => [
                    'Content-Type' => $response->headers->get('Content-Type'),
                ]
            ], now()->addMinutes(self::TTL_MINUTES));

            $this->rememberPath($request->path(), $key);

            $response->headers->set('X-Laravel-ISR', 'MISS');
        }

        return $response;
    }

    /**
     * Purge cached full-page HTML for one or more request paths
     * (e.g. "en/testing-page", "fr/education/foo-bar").
     *
     * Admin edits must call this, otherwise stale HTML is served for up to
     * TTL_MINUTES after the underlying content changed.
     */
    public static function forgetPath(string ...$paths): void
    {
        foreach ($paths as $path) {
            $path = trim($path, '/');
            if ($path === '') {
                continue;
            }

            try {
                $indexKey = self::pathIndexKey($path);
                $keys = Cache::get($indexKey);

                if (is_array($keys)) {
                    foreach (array_keys($keys) as $snapshotKey) {
                        Cache::forget((string) $snapshotKey);
                    }
                }

                Cache::forget($indexKey);
            } catch (\Throwable) {
                // A cache outage must never break an admin action.
            }
        }
    }

    protected static function pathIndexKey(string $path): string
    {
        return 'isr_path_' . md5($path);
    }

    /**
     * Remember which snapshot key belongs to a request path so it can be
     * purged later. The index expires with the snapshot itself.
     */
    protected function rememberPath(string $path, string $key): void
    {
        $path = trim($path, '/');
        if ($path === '') {
            return;
        }

        try {
            $indexKey = self::pathIndexKey($path);
            $keys = Cache::get($indexKey);
            $keys = is_array($keys) ? $keys : [];
            $keys[$key] = 1;

            // Hard cap: stale entries are harmless (they expire with the snapshot).
            if (count($keys) > 50) {
                $keys = array_slice($keys, -20, null, true);
            }

            Cache::put($indexKey, $keys, now()->addMinutes(self::TTL_MINUTES));
        } catch (\Throwable) {
            // Indexing is best-effort; the snapshot itself is already stored.
        }
    }
}