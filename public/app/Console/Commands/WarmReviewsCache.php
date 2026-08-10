<?php

namespace App\Console\Commands;

use App\Services\ReviewService;
use Illuminate\Console\Command;

class WarmReviewsCache extends Command
{
    protected $signature = 'reviews:warm-cache {--handle= : Warm cache for a single canonical handle only}';

    protected $description = 'Pre-fetch Judge.me reviews into cache so product pages load instantly';

    public function handle(ReviewService $reviews): int
    {
        if (config('judgeme.api_token') === '') {
            $this->warn('JUDGEME_API_TOKEN is not set — skipping.');

            return self::SUCCESS;
        }

        $handle = $this->option('handle');

        if ($handle) {
            $this->info("Warming reviews cache for: {$handle}");
            $reviews->warmCacheForHandle($handle);
            $this->info('Done.');

            return self::SUCCESS;
        }

        $handles = ReviewService::canonicalHandlesForWarmup();
        $this->info('Warming reviews cache for '.count($handles).' product groups…');

        $bar = $this->output->createProgressBar(count($handles));
        $bar->start();

        foreach ($handles as $canonical) {
            $reviews->warmCacheForHandle($canonical);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Review cache warmup complete.');

        return self::SUCCESS;
    }
}
