<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supabase\Product;
use App\Models\Supabase\LandingPage;
use App\Models\Supabase\ProductBundle;
use App\Models\Supabase\WebhookLog;
use App\Models\Supabase\UserActivityLog;
use App\Support\SupabaseDb;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Check if database is online
        $dbTest = SupabaseDb::run(fn() => Product::first(), 'fail');
        if ($dbTest === 'fail') {
            session()->flash('error', '⚠️ Supabase database connection timed out/failed. Please check your internet connection or .env connection credentials.');
        }

        $metrics = [
            'products_count'      => SupabaseDb::run(fn () => Product::count(), 0),
            'landings_count'      => SupabaseDb::run(fn () => LandingPage::count(), 0),
            'bundles_count'       => SupabaseDb::run(fn () => ProductBundle::count(), 0),
            'webhooks_pending'    => SupabaseDb::run(fn () => WebhookLog::where('status', 'pending')->count(), 0),
            'webhooks_failed'     => SupabaseDb::run(fn () => WebhookLog::where('status', 'failed')->count(), 0),
            'webhooks_dead'       => SupabaseDb::run(fn () => WebhookLog::where('status', 'dead')->count(), 0),
            'recent_activities'   => SupabaseDb::run(fn () => UserActivityLog::orderBy('created_at', 'desc')->limit(10)->get(), collect()),
        ];

        return view('admin.dashboard', compact('metrics'));
    }
}
