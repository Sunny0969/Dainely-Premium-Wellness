<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleMiddleware
{
    protected array $supported = ['en', 'fr', 'de'];

    public function handle(Request $request, Closure $next)
    {
        // 1. From route parameter
        $locale = $request->route('locale');

        // 2. From session
        if (!$locale || !in_array($locale, $this->supported)) {
            $locale = Session::get('locale');
        }

        // 3. From cookie
        if (!$locale || !in_array($locale, $this->supported)) {
            $locale = $request->cookie('locale');
        }

        // 4. From Accept-Language header
        if (!$locale || !in_array($locale, $this->supported)) {
            $browserLocale = substr($request->header('Accept-Language', 'en'), 0, 2);
            $locale = in_array($browserLocale, $this->supported) ? $browserLocale : 'en';
        }

        $locale = in_array($locale, $this->supported) ? $locale : 'en';

        App::setLocale($locale);
        Session::put('locale', $locale);

        $response = $next($request);

        // Store locale in cookie (1 year)
        return $response->withCookie(cookie('locale', $locale, 525600));
    }
}
