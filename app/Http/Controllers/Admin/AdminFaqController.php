<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supabase\Faq;
use App\Models\Supabase\Product;
use App\Models\Supabase\LandingPage;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminFaqController extends Controller
{
    public function index(Request $request)
    {
        if (!SupabaseDb::available()) {
            session()->flash('error', '⚠️ Supabase database connection failed. FAQs manager offline.');
        }

        $faqs = SupabaseDb::run(function() use ($request) {
            $query = Faq::query()->with('faqable');

            if ($request->filled('locale')) {
                $query->where('locale', $request->query('locale'));
            }

            return $query->orderBy('sort_order')->paginate(20);
        }, new LengthAwarePaginator([], 0, 20));

        $products = SupabaseDb::run(fn() => Product::orderBy('title')->get(), collect());
        $landingPages = SupabaseDb::run(fn() => LandingPage::orderBy('title')->get(), collect());

        return view('admin.faqs.index', compact('faqs', 'products', 'landingPages'));
    }

    public function store(Request $request)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot create FAQ.');
        }

        return SupabaseDb::run(function () use ($request) {
            $validated = $request->validate([
                'faqable_type' => 'required|string',
                'faqable_id'   => 'required|integer',
                'locale'       => 'required|string|size:2',
                'question'     => 'required|string',
                'answer'       => 'required|string',
                'sort_order'   => 'required|integer',
            ]);

            Faq::create($validated);

            return back()->with('success', 'FAQ created successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function update(Request $request, int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update FAQ.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $faq = Faq::findOrFail($id);

            $validated = $request->validate([
                'question'   => 'required|string',
                'answer'     => 'required|string',
                'sort_order' => 'required|integer',
                'approved'   => 'required|boolean',
            ]);

            $faq->update($validated);

            return back()->with('success', "FAQ #{$id} updated successfully!");
        }, back()->with('error', 'Database operation failed.'));
    }

    public function destroy(int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot delete FAQ.');
        }

        return SupabaseDb::run(function () use ($id) {
            $faq = Faq::findOrFail($id);
            $faq->delete();

            return back()->with('success', 'FAQ deleted successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }
}
