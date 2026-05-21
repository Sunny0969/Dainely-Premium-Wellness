<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductTranslation;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(string $locale)
    {
        $products = Product::with('translations')
            ->active()
            ->orderBy('sort_order')
            ->get();

        return view('pages.products.index', compact('products', 'locale'));
    }

    public function show(string $locale, string $slug)
    {
        // Find by translation slug
        $translation = ProductTranslation::where('slug', $slug)->firstOrFail();
        $product = $translation->product ?? Product::with(['translations', 'testimonials'])->findOrFail($translation->product_id);

        if (!$product->relationLoaded('translations')) {
            $product->load('translations', 'testimonials');
        }

        // Related product (the other one)
        $related = Product::with('translations')
            ->active()
            ->where('id', '!=', $product->id)
            ->first();

        // Testimonials for this product
        $testimonials = Testimonial::where('is_active', true)
            ->orderBy('sort_order')
            ->limit(3)
            ->get();

        return view('pages.products.show', compact('product', 'related', 'testimonials', 'locale'));
    }
}
