<?php

namespace App\Http\Middleware;

use App\Services\GeoLocaleService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LocaleMiddleware
{
    protected array $supported = ['en', 'fr', 'de'];

    public function __construct(protected GeoLocaleService $geo) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->getHost() === '127.0.0.1' && app()->environment('local') && $request->isMethod('GET')) {
            return redirect()->to(str_replace('127.0.0.1', 'localhost', $request->fullUrl()));
        }

        $routeLocale = $request->route('locale');
        $locale      = is_string($routeLocale) && in_array($routeLocale, $this->supported, true)
            ? $routeLocale
            : null;

        if ($this->shouldGeoRedirectToRegionalLocale($request, $locale)) {
            $detected = $this->geo->detectLocaleFromRequest($request);

            if (in_array($detected, ['fr', 'de'], true)) {
                return $this->redirectToLocale($request, $detected);
            }
        }

        if ($locale === null) {
            $locale = Session::get('locale');
        }

        if (! is_string($locale) || ! in_array($locale, $this->supported, true)) {
            $locale = $request->cookie('locale');
        }

        if (! is_string($locale) || ! in_array($locale, $this->supported, true)) {
            $locale = $this->geo->detectLocaleFromRequest($request);
        }

        $locale = in_array($locale, $this->supported, true) ? $locale : 'en';

        App::setLocale($locale);
        Session::put('locale', $locale);

        $country = $this->geo->resolveCountryCode($request);
        if (is_string($country) && $country !== '') {
            Session::put('geo_country', $country);
        }

        $response = $next($request);

        return $response->withCookie(cookie('locale', $locale, 525600));
    }

    protected function shouldGeoRedirectToRegionalLocale(Request $request, ?string $routeLocale): bool
    {
        return $request->isMethod('GET')
            && ! $request->hasCookie('locale')
            && $routeLocale === 'en'
            && config('geo.enabled', true);
    }

    protected function redirectToLocale(Request $request, string $locale): Response
    {
        $route = $request->route();

        if ($route && $route->getName()) {
            $params = array_merge($route->parameters(), ['locale' => $locale]);

            return redirect()
                ->route($route->getName(), $params)
                ->withCookie(cookie('locale', $locale, 525600));
        }

        $path = $request->path();
        $suffix = $path === 'en' ? '' : substr($path, 2);

        return redirect('/' . $locale . $suffix)
            ->withCookie(cookie('locale', $locale, 525600));
    }
}
