<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;

class EducationController extends Controller
{
    protected function featuredProduct()
    {
        return Product::with('translations')->active()->featured()->first();
    }

    public function backPain(string $locale)
    {
        $product = $this->featuredProduct();
        return view('education.back-pain', compact('locale', 'product'));
    }

    public function sciatica(string $locale)
    {
        $product = $this->featuredProduct();
        return view('education.sciatica', compact('locale', 'product'));
    }

    public function posture(string $locale)
    {
        $product = $this->featuredProduct();
        return view('education.posture', compact('locale', 'product'));
    }

    public function neckPain(string $locale)
    {
        $product = $this->featuredProduct();
        return view('education.neck-pain', compact('locale', 'product'));
    }

    public function mobility(string $locale)
    {
        $product = $this->featuredProduct();
        return view('education.mobility', compact('locale', 'product'));
    }

    public function recovery(string $locale)
    {
        $product = $this->featuredProduct();
        return view('education.recovery', compact('locale', 'product'));
    }
}
