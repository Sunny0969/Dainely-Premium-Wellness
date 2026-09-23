<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class StaticIsrCache
{
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
            ], now()->addMinutes(60));
            
            $response->headers->set('X-Laravel-ISR', 'MISS');
        }

        return $response;
    }
}