<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    public function about(string $locale)
    {
        return view('pages.about', compact('locale'));
    }

    public function faq(string $locale)
    {
        // Group FAQs by category
        $faqs = Faq::with('translations')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');

        return view('pages.faq', compact('faqs', 'locale'));
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

        // Log the contact (in production, send email via Mail::)
        Log::info('Contact form submission', [
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'subject'      => $validated['subject'],
            'order_number' => $validated['order_number'] ?? null,
            'message'      => substr($validated['message'], 0, 200),
        ]);

        return redirect()
            ->route('contact', ['locale' => $locale])
            ->with('success', 'Thank you! Your message has been received. We\'ll respond within 4 business hours.');
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
        return response()
            ->view('sitemap.locale', compact('locale'))
            ->header('Content-Type', 'application/xml');
    }
}
