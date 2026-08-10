<?php

namespace App\Listeners;

use App\Events\AnalyticsEventOccurred;
use App\Jobs\SendGa4MeasurementProtocolJob;
use App\Jobs\SendMetaConversionJob;

/**
 * Phase 2 §11.1 — keep request fast; export via queued jobs.
 */
class DispatchAnalyticsExportJobs
{
    public function handle(AnalyticsEventOccurred $event): void
    {
        $exportable = config('analytics.export_events', []);

        if (! in_array($event->eventName, $exportable, true)) {
            return;
        }

        SendGa4MeasurementProtocolJob::dispatch(
            $event->eventName,
            $event->data,
            $event->sessionId
        );

        SendMetaConversionJob::dispatch(
            $event->eventName,
            $event->data,
            $event->sessionId
        );
    }
}
