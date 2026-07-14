<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\AnalyticsService;
use Symfony\Component\HttpFoundation\Response;

class TrackPageViews
{
    public function __construct(protected AnalyticsService $analytics)
    {
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log GET requests that are HTML responses and not admin/API/debugbar requests
        if ($request->isMethod('GET') 
            && !$request->expectsJson() 
            && !$request->is('api/*') 
            && !$request->is('admin/*') 
            && !$request->is('_debugbar/*')
        ) {
            try {
                $this->analytics->logEvent('page_view', [
                    'locale' => app()->getLocale(),
                    'path'   => $request->path(),
                ]);
            } catch (\Exception $e) {
                // Fail silently to avoid breaking the user experience on log failures
                logger()->error("Failed to log page view event: " . $e->getMessage());
            }
        }

        return $response;
    }
}
