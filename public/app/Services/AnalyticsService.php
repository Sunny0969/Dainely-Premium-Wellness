<?php

namespace App\Services;

use App\Models\Supabase\UserActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Legacy facade — prefer AnalyticsEventService::track() (§11.1).
 */
class AnalyticsService
{
    public function __construct(protected AnalyticsEventService $events) {}

    /**
     * @deprecated Use AnalyticsEventService::track()
     */
    public function logEvent(string $eventName, array $eventData = []): void
    {
        $this->events->track($eventName, array_merge([
            'user_agent' => request()?->userAgent(),
            'ip' => request()?->ip(),
        ], $eventData));
    }

    public function logActivity(string $eventType, ?string $itemType = null, ?int $itemId = null, array $context = []): ?UserActivityLog
    {
        try {
            return UserActivityLog::create([
                'visitor_id' => Session::getId(),
                'user_id' => Auth::id(),
                'event_type' => $eventType,
                'item_type' => $itemType,
                'item_id' => $itemId,
                'context' => array_merge([
                    'url' => request()?->fullUrl(),
                    'ip' => request()?->ip(),
                ], $context),
                'created_at' => now(),
            ]);
        } catch (\Throwable) {
            return null;
        }
    }
}
