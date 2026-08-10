<?php

namespace App\Http\Middleware;

use App\Services\AnalyticsEventService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackPageViews
{
    public function __construct(protected AnalyticsEventService $analytics) {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Generic page_view stays in DB only (not in export_events).
        // Defer so TTFB is not blocked by remote Supabase latency.
        if ($request->isMethod('GET')
            && ! $request->expectsJson()
            && ! $request->is('api/*')
            && ! $request->is('dainely-admin-panel')
            && ! $request->is('dainely-admin-panel/*')
            && ! $request->is('health/*')
            && ! $request->is('_debugbar/*')
        ) {
            $payload = [
                'path' => $request->path(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'locale' => app()->getLocale(),
            ];

            dispatch(function () use ($payload) {
                try {
                    app(AnalyticsEventService::class)->track('page_view', $payload);
                } catch (\Throwable $e) {
                    logger()->error('Failed to log page view event: '.$e->getMessage());
                }
            })->afterResponse();
        }

        return $response;
    }
}
