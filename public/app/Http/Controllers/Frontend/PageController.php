<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    public function about(string $locale)
    {
        return view('pages.about', compact('locale'));
    }

    public function faq(string $locale)
    {
        return view('pages.faq', compact('locale'));
    }

    public function contact(string $locale)
    {
        return view('pages.contact', compact('locale'));
    }

    public function contactSubmit(Request $request, string $locale)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|max:255',
            'subject'      => 'required|string|max:100',
            'order_number' => 'nullable|string|max:50',
            'message'      => 'required|string|min:10|max:2000',
        ]);

        Log::info('Contact form submission', [
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'subject'      => $validated['subject'],
            'order_number' => $validated['order_number'] ?? null,
            'message'      => substr($validated['message'], 0, 200),
        ]);

        app(\App\Services\AnalyticsEventService::class)->track('contact_form', [
            'subject' => $validated['subject'],
            'email' => $validated['email'],
            'has_order_number' => ! empty($validated['order_number']),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('contact', ['locale' => $locale])
            ->with('success', 'Thank you! Your message has been received. We\'ll respond within 4 business hours.');
    }

    public function newsletterSubscribe(Request $request, string $locale)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        app(\App\Services\AnalyticsEventService::class)->track('newsletter_signup', [
            'email' => $validated['email'],
            'source' => 'footer',
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->back()
            ->with('newsletter_success', __('footer.subscribe_success'));
    }

    public function privacy(string $locale)
    {
        return view('pages.legal.privacy', compact('locale'));
    }

    public function terms(string $locale)
    {
        return view('pages.legal.terms', compact('locale'));
    }

    public function shipping(string $locale)
    {
        return view('pages.legal.shipping', compact('locale'));
    }

    public function refund(string $locale)
    {
        return view('pages.legal.refund', compact('locale'));
    }

    public function sitemapIndex()
    {
        $locales = ['en', 'fr', 'de'];
        return response()
            ->view('sitemap.index', compact('locales'))
            ->header('Content-Type', 'application/xml');
    }

    public function sitemap(string $locale)
    {
        $urls = [];

        // Static routes
        $urls[] = route('home', ['locale' => $locale]);
        $urls[] = route('products.index', ['locale' => $locale]);
        $urls[] = route('about', ['locale' => $locale]);
        $urls[] = route('contact', ['locale' => $locale]);
        $urls[] = route('faq', ['locale' => $locale]);
        $urls[] = route('privacy', ['locale' => $locale]);
        $urls[] = route('terms', ['locale' => $locale]);
        $urls[] = route('shipping', ['locale' => $locale]);
        $urls[] = route('refund', ['locale' => $locale]);

        // Education routes
        $urls[] = route('education.back-pain', ['locale' => $locale]);
        $urls[] = route('education.sciatica', ['locale' => $locale]);
        $urls[] = route('education.posture', ['locale' => $locale]);
        $urls[] = route('education.neck-pain', ['locale' => $locale]);
        $urls[] = route('education.mobility', ['locale' => $locale]);
        $urls[] = route('education.recovery', ['locale' => $locale]);

        // Dynamic Products from Supabase
        if (\App\Support\SupabaseDb::available()) {
            $products = \App\Models\Supabase\Product::where('status', 'active')->get();
            foreach ($products as $product) {
                $urls[] = route('products.show', ['locale' => $locale, 'slug' => $product->handle]);
            }

            // Dynamic Landing Pages
            $landingPages = \App\Models\Supabase\LandingPage::where('locale', $locale)->published()->get();
            foreach ($landingPages as $page) {
                $urls[] = route('landing.show', ['locale' => $locale, 'slug' => $page->slug]);
            }
        }

        return response()
            ->view('sitemap.locale', compact('urls', 'locale'))
            ->header('Content-Type', 'application/xml');
    }

    public function llmsTxt(string $locale)
    {
        $path = public_path("llms_{$locale}.txt");
        if (file_exists($path)) {
            return response()->file($path, ['Content-Type' => 'text/plain']);
        }

        abort(404);
    }
}
