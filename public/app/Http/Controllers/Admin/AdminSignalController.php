<?php

namespace App\Http\Controllers\Admin;

use App\Models\Supabase\ProductKnowledgeSignal;
use App\Support\SupabaseDb;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminSignalController extends AdminController
{
    public function index(Request $request)
    {
        $this->flashIfSupabaseOffline('AI signals manager');

        $signals = SupabaseDb::run(function() use ($request) {
            $query = ProductKnowledgeSignal::query()->with(['product:id,title']);

            if ($request->filled('locale')) {
                $query->where('locale', $request->query('locale'));
            }

            if ($request->filled('approved')) {
                $query->where('approved', $request->boolean('approved'));
            }

            return $query->orderBy('created_at', 'desc')->paginate(20);
        }, new LengthAwarePaginator([], 0, 20));

        return view('admin.signals.index', compact('signals'));
    }

    public function toggleApproval(int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot toggle approval.');
        }

        return SupabaseDb::run(function () use ($id) {
            $signal = ProductKnowledgeSignal::findOrFail($id);
            $signal->approved = !$signal->approved;
            $signal->save();

            if ($signal->product) {
                \App\Support\StorefrontCache::forgetProduct(
                    (string) $signal->product->handle,
                    (int) $signal->product_id
                );
            }

            return back()->with('success', "Signal approved status updated successfully!");
        }, fn () => back()->with('error', 'Database operation failed.'));
    }

    public function update(Request $request, int $id)
    {
        if (!SupabaseDb::available()) {
            return back()->with('error', 'Database offline. Cannot update signal.');
        }

        return SupabaseDb::run(function () use ($request, $id) {
            $signal = ProductKnowledgeSignal::findOrFail($id);
            
            $validated = $request->validate([
                'question'     => 'required|string',
                'answer'       => 'required|string',
                'speaker_type' => 'required|string|in:expert,customer,ai',
            ]);

            $signal->update($validated);

            if ($signal->product) {
                \App\Support\StorefrontCache::forgetProduct(
                    (string) $signal->product->handle,
                    (int) $signal->product_id
                );
            }

            return back()->with('success', "Signal #{$id} updated successfully.");
        }, fn () => back()->with('error', 'Database operation failed.'));
    }
}
