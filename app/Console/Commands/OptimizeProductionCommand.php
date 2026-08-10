<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Production Laravel caches (config / route / view) + OPcache status hint.
 * Run on the server after deploy — not required for local `artisan serve`.
 */
class OptimizeProductionCommand extends Command
{
    protected $signature = 'optimize:production
                            {--clear : Clear caches instead of building them}
                            {--force : Run even when APP_ENV=local (no confirmation)}';

    protected $description = 'Cache config, routes, and views for production (or clear them)';

    public function handle(): int
    {
        if ($this->option('clear')) {
            Artisan::call('optimize:clear');
            $this->line(Artisan::output());
            $this->info('Production caches cleared.');

            return self::SUCCESS;
        }

        if (
            $this->laravel->environment('local')
            && ! $this->option('force')
            && ! $this->confirm('You are in local env. Cache config/routes/views anyway?', false)
        ) {
            $this->warn('Skipped. Use this on production after deploy.');

            return self::SUCCESS;
        }

        $steps = [
            'config:cache' => 'Configuration',
            'route:cache'  => 'Routes',
            'view:cache'   => 'Views',
            'event:cache'  => 'Events',
        ];

        foreach ($steps as $command => $label) {
            $this->info("Caching {$label}…");
            $exit = Artisan::call($command);
            $this->line(trim(Artisan::output()));
            if ($exit !== 0) {
                $this->error("{$command} failed (exit {$exit}).");

                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('Laravel production caches ready.');
        $this->comment('Also ensure PHP OPcache is enabled (see docs/Deployment.md).');

        if (function_exists('opcache_get_status')) {
            $status = @opcache_get_status(false);
            if (is_array($status) && ($status['opcache_enabled'] ?? false)) {
                $this->info('OPcache: enabled');
            } else {
                $this->warn('OPcache: not enabled in this PHP process — ask hosting to enable opcache.enable=1');
            }
        } else {
            $this->warn('OPcache extension not loaded in this PHP process.');
        }

        return self::SUCCESS;
    }
}
