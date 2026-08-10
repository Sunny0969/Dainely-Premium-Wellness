<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Phase 2 §11.1 — fired after analytics_events row is written.
 * Listeners enqueue GA4 / Meta (and future) export jobs.
 */
class AnalyticsEventOccurred
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $eventName,
        public array $data,
        public ?string $sessionId = null,
    ) {}
}
