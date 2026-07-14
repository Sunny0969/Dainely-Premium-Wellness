<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supabase\RelatedContent;
use App\Models\Supabase\Product;
use App\Models\Supabase\LandingPage;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;

class AdminRelatedController extends Controller
{
    public function index()
    {
        if (!SupabaseDb::available()) {
            session()->flash('error', '⚠️ Supabase database connection failed. Internal links manager offline.');
        }

        $relations = SupabaseDb::run(function() {
            return RelatedContent::orderBy('source_type')
                ->orderBy('display_order')
                ->get();
        }, collect());

        $products = SupabaseDb::run(fn() => Product::orderBy('title')->get(), collect());
        $landingPages = SupabaseDb::run(fn() => LandingPage::orderBy('title')->get(), collect());

        return view('admin.related.index', compact('relations', 'products', 'landingPages'));
    }

    public function store(Request $request)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot create link.');
        }

        return SupabaseDb::run(function () use ($request) {
            $validated = $request->validate([
                'source_type'   => 'required|string|in:product,landing_page',
                'source_id'     => 'required|integer',
                'related_type'  => 'required|string|in:product,landing_page',
                'related_id'    => 'required|integer',
                'display_order' => 'required|integer',
            ]);

            // Prevent linking the exact same resource to itself
            if ($validated['source_type'] === $validated['related_type'] && $validated['source_id'] === $validated['related_id']) {
                return back()->with('error', 'Cannot link a resource to itself.');
            }

            // Check if relation already exists
            $exists = RelatedContent::where('source_type', $validated['source_type'])
                ->where('source_id', $validated['source_id'])
                ->where('related_type', $validated['related_type'])
                ->where('related_id', $validated['related_id'])
                ->exists();

            if ($exists) {
                return back()->with('error', 'This content relationship link already exists.');
            }

            RelatedContent::create($validated);

            return back()->with('success', 'Content relationship link created successfully!');
        }, back()->with('error', 'Database operation failed.'));
    }

    public function destroy(int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot remove link.');
        }

        return SupabaseDb::run(function () use ($id) {
            $relation = RelatedContent::findOrFail($id);
            $relation->delete();

            return back()->with('success', 'Content relationship link deleted.');
        }, back()->with('error', 'Database operation failed.'));
    }
}
