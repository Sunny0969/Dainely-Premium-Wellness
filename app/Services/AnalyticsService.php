<?php

namespace App\Services;

use App\Models\Supabase\AnalyticsEvent;
use App\Models\Supabase\UserActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AnalyticsService
{
    /**
     * Log a general analytics event.
     */
    public function logEvent(string $eventName, array $eventData = []): AnalyticsEvent
    {
        return AnalyticsEvent::create([
            'event_name' => $eventName,
            'event_data' => array_merge([
                'url'        => request()->fullUrl(),
                'user_agent' => request()->userAgent(),
                'ip'         => request()->ip(),
            ], $eventData),
            'session_id'  => Session::getId(),
            'user_id'     => Auth::id(),
            'occurred_at' => now(),
        ]);
    }

    /**
     * Log a specific user activity.
     */
    public function logActivity(string $eventType, ?string $itemType = null, ?int $itemId = null, array $context = []): UserActivityLog
    {
        return UserActivityLog::create([
            'visitor_id' => Session::getId(), // session acts as visitor identifier
            'user_id'    => Auth::id(),
            'event_type' => $eventType,
            'item_type'  => $itemType,
            'item_id'    => $itemId,
            'context'    => array_merge([
                'url' => request()->fullUrl(),
                'ip'  => request()->ip(),
            ], $context),
            'created_at' => now(),
        ]);
    }
}
