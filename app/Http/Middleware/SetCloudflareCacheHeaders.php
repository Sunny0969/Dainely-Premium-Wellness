<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Emit Cache-Control / CDN-Cache-Control so Cloudflare can edge-cache
 * static CMS, blog, education, and legal HTML (Full Page Cache).
 *
 * Do NOT use on cart, checkout, product, or admin routes.
 */
class SetCloudflareCacheHeaders
{
    public function handle(Request $request, Closure $next, string $profile = 'html'): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! filter_var(config('cloudflare.edge_cache_enabled', true), FILTER_VALIDATE_BOOLEAN)) {
            return $response;
        }

        if (! $request->isMethodCacheable() || $response->getStatusCode() !== 200) {
            return $response;
        }

        // Never cache authenticated admin or requests that already opted out.
        if ($request->is('dainely-admin-panel', 'dainely-admin-panel/*')) {
            return $this->noStore($response);
        }

        if ($profile === 'assets') {
            $ttl = max(60, (int) config('cloudflare.assets_edge_ttl', 31536000));

            return $this->apply($response, $ttl, $ttl);
        }

        $edgeTtl = max(0, (int) config('cloudflare.html_edge_ttl', 300));
        $browserTtl = max(0, (int) config('cloudflare.html_browser_ttl', 0));

        return $this->apply($response, $edgeTtl, $browserTtl);
    }

    protected function apply(Response $response, int $edgeTtl, int $browserTtl): Response
    {
        if ($edgeTtl < 1) {
            return $this->noStore($response);
        }

        // Browser: usually no long cache; CDN: s-maxage / CDN-Cache-Control.
        $parts = ['public'];
        $parts[] = 'max-age='.$browserTtl;
        $parts[] = 's-maxage='.$edgeTtl;
        $parts[] = 'stale-while-revalidate='.min(60, $edgeTtl);

        $response->headers->set('Cache-Control', implode(', ', $parts));
        $response->headers->set('CDN-Cache-Control', 'public, max-age='.$edgeTtl);
        $response->headers->set('Cloudflare-CDN-Cache-Control', 'public, max-age='.$edgeTtl);

        // Do not strip Set-Cookie here — Laravel sessions still work.
        // Cloudflare Cache Rules must "Ignore cookies" / Eligible for cache on these paths
        // (see docs/Cloudflare.md). Responses with Set-Cookie are otherwise often bypassed.

        $response->headers->set('Vary', 'Accept-Encoding');

        return $response;
    }

    protected function noStore(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'private, no-store, no-cache, must-revalidate');
        $response->headers->set('CDN-Cache-Control', 'no-store');

        return $response;
    }
}
