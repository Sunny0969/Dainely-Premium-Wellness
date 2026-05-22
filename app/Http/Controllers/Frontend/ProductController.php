<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Support\StaticCatalog;
// use App\Models\Product;
// use App\Models\ProductTranslation;
// use App\Models\Testimonial;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(string $locale)
    {
        // Database disabled — static catalog
        // $products = Product::with('translations')
        //     ->active()
        //     ->orderBy('sort_order')
        //     ->get();
        $products = StaticCatalog::products();

        return view('pages.products.index', compact('products', 'locale'));
    }

    public function show(string $locale, string $slug)
    {
        // Database disabled — resolve product by slug from static catalog
        // $translation = ProductTranslation::where('slug', $slug)->firstOrFail();
        // $product = $translation->product ?? Product::with(['translations', 'testimonials'])->findOrFail($translation->product_id);
        // if (!$product->relationLoaded('translations')) {
        //     $product->load('translations', 'testimonials');
        // }
        // $related = Product::with('translations')
        //     ->active()
        //     ->where('id', '!=', $product->id)
        //     ->first();
        // $testimonials = Testimonial::where('is_active', true)
        //     ->orderBy('sort_order')
        //     ->limit(3)
        //     ->get();

        $product = StaticCatalog::findBySlug($slug, $locale);
        if (!$product) {
            abort(404);
        }

        $related = StaticCatalog::products()
            ->first(fn ($p) => $p->id !== $product->id);

        return view('pages.products.show', compact('product', 'related', 'locale'));
    }
}
