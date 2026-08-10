<?php

namespace App\Services;

use App\Events\AnalyticsEventOccurred;
use App\Models\Supabase\AnalyticsEvent;
use App\Support\SupabaseDb;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Phase 2 §11.1 — Unified Event Service.
 * Persist + dispatch domain event; listeners send to GA4 / Meta async.
 */
class AnalyticsEventService
{
    public function __construct(protected GeoLocaleService $geo) {}

    public function track(string $eventName, array $data = []): void
    {
        if (! config('analytics.enabled', true)) {
            return;
        }

        $data['locale'] = $data['locale'] ?? app()->getLocale();
        $data['country_code'] = $data['country_code']
            ?? session('country_code')
            ?? session('geo_country')
            ?? $this->resolveCountryCode();

        if (! empty($data['country_code'])) {
            session(['country_code' => $data['country_code'], 'geo_country' => $data['country_code']]);
        }

        if (! isset($data['url']) && request()) {
            $data['url'] = request()->fullUrl();
        }

        $sessionId = session()->getId();

        try {
            if (SupabaseDb::available()) {
                AnalyticsEvent::create([
                    'event_name' => $eventName,
                    'event_data' => $data,
                    'session_id' => $sessionId,
                    'user_id' => Auth::id(),
                    'occurred_at' => now(),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('AnalyticsEventService: failed to persist event', [
                'event' => $eventName,
                'error' => $e->getMessage(),
            ]);
        }

        // Always fire domain event so export jobs can run even if DB write failed.
        event(new AnalyticsEventOccurred($eventName, $data, $sessionId));
    }

    protected function resolveCountryCode(): ?string
    {
        try {
            // Docs reference geoip(); we use existing GeoLocaleService (no extra package).
            return $this->geo->resolveCountryCode(request());
        } catch (Throwable) {
            return null;
        }
    }
}
