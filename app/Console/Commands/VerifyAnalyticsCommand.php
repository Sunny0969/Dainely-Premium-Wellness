<?php

namespace App\Console\Commands;

use App\Events\AnalyticsEventOccurred;
use App\Jobs\SendGa4MeasurementProtocolJob;
use App\Jobs\SendMetaConversionJob;
use App\Listeners\DispatchAnalyticsExportJobs;
use App\Models\Supabase\AnalyticsEvent;
use App\Services\AnalyticsEventService;
use App\Support\SupabaseDb;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;

class VerifyAnalyticsCommand extends Command
{
    protected $signature = 'analytics:verify {--track : Fire a sample contact_form track}';

    protected $description = 'Verify Phase 2 §11 analytics unified event service + export listeners';

    public function handle(AnalyticsEventService $analytics): int
    {
        $checks = [
            ['AnalyticsEventService', class_exists(AnalyticsEventService::class) ? 'OK' : 'MISSING'],
            ['AnalyticsEventOccurred', class_exists(AnalyticsEventOccurred::class) ? 'OK' : 'MISSING'],
            ['DispatchAnalyticsExportJobs', class_exists(DispatchAnalyticsExportJobs::class) ? 'OK' : 'MISSING'],
            ['SendGa4MeasurementProtocolJob', class_exists(SendGa4MeasurementProtocolJob::class) ? 'OK' : 'MISSING'],
            ['SendMetaConversionJob', class_exists(SendMetaConversionJob::class) ? 'OK' : 'MISSING'],
            ['Listener registered', $this->listenerRegistered() ? 'OK' : 'MISSING'],
            ['GA4 measurement id', config('analytics.ga4.measurement_id') ? 'set' : 'empty (jobs no-op until set)'],
            ['GA4 api secret', config('analytics.ga4.api_secret') ? 'set' : 'empty (jobs no-op until set)'],
            ['Meta pixel', config('analytics.meta.pixel_id') ? 'set' : 'empty (jobs no-op until set)'],
            ['Meta token', config('analytics.meta.access_token') ? 'set' : 'empty (jobs no-op until set)'],
            ['Supabase', SupabaseDb::available() ? 'OK' : 'unavailable'],
        ];

        if ($this->option('track') && SupabaseDb::available()) {
            $before = AnalyticsEvent::count();
            $analytics->track('contact_form', [
                'subject' => 'analytics:verify',
                'email' => 'verify@dainely.local',
                'source' => 'artisan',
            ]);
            $after = AnalyticsEvent::count();
            $checks[] = ['Sample track persisted', $after > $before ? 'OK (+1)' : 'WARN (no new row)'];
            $checks[] = ['Latest event', (string) (AnalyticsEvent::orderByDesc('id')->value('event_name') ?? '—')];
        } elseif ($this->option('track')) {
            $checks[] = ['Sample track', 'skipped (Supabase unavailable)'];
        }

        if (SupabaseDb::available()) {
            $byName = AnalyticsEvent::query()
                ->selectRaw('event_name, count(*) as c')
                ->groupBy('event_name')
                ->orderByDesc('c')
                ->limit(12)
                ->pluck('c', 'event_name');
            $checks[] = ['Indexed events', $byName->map(fn ($c, $n) => "{$n}:{$c}")->implode(' | ') ?: 'none yet'];
        }

        $this->table(['Check', 'Result'], $checks);

        $ok = class_exists(AnalyticsEventService::class)
            && class_exists(AnalyticsEventOccurred::class)
            && $this->listenerRegistered();

        if (! $ok) {
            $this->error('§11 VERIFY FAILED');

            return self::FAILURE;
        }

        $this->info('§11 VERIFY PASSED');
        $this->line('Standard events: product_view, add_to_cart, begin_checkout, purchase, landing_page_view, education_view, newsletter_signup, contact_form');
        $this->line('Re-run with --track to write a sample row + dispatch export jobs (sync queue runs immediately).');

        return self::SUCCESS;
    }

    protected function listenerRegistered(): bool
    {
        $listeners = Event::getListeners(AnalyticsEventOccurred::class);

        foreach ($listeners as $listener) {
            if (is_string($listener) && str_contains($listener, 'DispatchAnalyticsExportJobs')) {
                return true;
            }
            if (is_array($listener) && isset($listener[0]) && $listener[0] instanceof DispatchAnalyticsExportJobs) {
                return true;
            }
            if (is_object($listener) && $listener instanceof DispatchAnalyticsExportJobs) {
                return true;
            }
        }

        // Laravel wraps listeners — check EventServiceProvider mapping
        $provider = app()->getProvider(\App\Providers\EventServiceProvider::class);
        if ($provider) {
            $listen = (new \ReflectionClass($provider))->getProperty('listen');
            $listen->setAccessible(true);
            $map = $listen->getValue($provider);
            $targets = $map[AnalyticsEventOccurred::class] ?? [];

            return in_array(DispatchAnalyticsExportJobs::class, $targets, true);
        }

        return false;
    }
}
