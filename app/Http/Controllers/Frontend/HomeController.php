<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();

        // Load featured products with translations
        $products = Product::with('translations')
            ->active()
            ->featured()
            ->orderBy('sort_order')
            ->get();

        // Load featured testimonials for this locale (fall back to all)
        $testimonials = Testimonial::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        return view('pages.home', compact('products', 'testimonials', 'locale'));
    }
}
