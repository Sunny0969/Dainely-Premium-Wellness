<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
// use App\Models\Product;
// use App\Models\Testimonial;

class HomeController extends Controller
{
    public function index()
    {
        $locale = app()->getLocale();

        // Database disabled — home view uses hardcoded products & testimonials
        // $products = Product::with('translations')
        //     ->active()
        //     ->featured()
        //     ->orderBy('sort_order')
        //     ->get();
        //
        // $testimonials = Testimonial::where('is_active', true)
        //     ->where('is_featured', true)
        //     ->orderBy('sort_order')
        //     ->limit(3)
        //     ->get();

        return view('pages.home', compact('locale'));
    }
}
