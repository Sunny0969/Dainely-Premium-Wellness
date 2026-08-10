<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->get('admin_authenticated')) {
            return redirect('/dainely-admin-panel/login')->with('error', 'Please log in to access the admin panel.');
        }

        return $next($request);
    }
}
