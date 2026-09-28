<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\Response;

class ClearCacheOnAdminSave
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            if ($response->isSuccessful() || $response->isRedirection()) {
                if (!$request->is('*/login', '*/logout')) {
                    try {
                        Artisan::call('cache:clear');
                        Artisan::call('view:clear');
                    } catch (\Exception $e) {
                        // ignore if it fails
                    }
                }
            }
        }

        return $response;
    }
}