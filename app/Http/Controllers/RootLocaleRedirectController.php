<?php

namespace App\Http\Controllers;

use App\Services\GeoLocaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RootLocaleRedirectController extends Controller
{
    /**
     * Root redirect — geolocate first-time visitors, then cookie/session.
     */
    public function __invoke(Request $request, GeoLocaleService $geo): RedirectResponse
    {
        $supported = ['en', 'fr', 'de'];
        $locale = $request->cookie('locale');

        if (! is_string($locale) || ! in_array($locale, $supported, true)) {
            $locale = $geo->detectLocaleFromRequest($request);
        }

        return redirect()
            ->route('home', ['locale' => $locale])
            ->withCookie(cookie('locale', $locale, 525600));
    }
}
