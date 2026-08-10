<?php

namespace App\Console\Commands;

use App\Models\Supabase\SearchIndex;
use App\Services\SearchService;
use App\Support\SupabaseDb;
use Illuminate\Console\Command;

class VerifySearchCommand extends Command
{
    protected $signature = 'search:verify {--reindex : Rebuild search_index for products, landings, education, blog} {--q=belt : Sample query to run}';

    protected $description = 'Verify Phase 2 §10 search infrastructure (index + FTS simple config)';

    public function handle(SearchService $search): int
    {
        if (! SupabaseDb::available()) {
            $this->error('Supabase unavailable (pdo_pgsql / connection)');

            return self::FAILURE;
        }

        if ($this->option('reindex')) {
            $this->info('Rebuilding search_index (sync jobs; may take a few minutes on remote Supabase)…');
            $counts = $search->reindexAll(true);
            $this->table(['Entity', 'Indexed'], [
                ['product', $counts['product']],
                ['landing_page', $counts['landing_page']],
                ['education', $counts['education']],
                ['blog', $counts['blog']],
            ]);
        }

        $total = SearchIndex::count();
        $byType = SearchIndex::query()
            ->selectRaw('searchable_type, count(*) as c')
            ->groupBy('searchable_type')
            ->pluck('c', 'searchable_type');

        $q = (string) $this->option('q');
        $results = $search->search($q, 'en');

        $this->table(['Check', 'Result'], [
            ['search_index rows', (string) $total],
            ['types present', $byType->keys()->map(fn ($t) => class_basename($t))->implode(', ') ?: 'none'],
            ['type counts', $byType->map(fn ($c, $t) => class_basename($t).':'.$c)->implode(' | ') ?: '—'],
            ["FTS q=\"{$q}\" (en)", $results->count().' hits (limit 20)'],
            ['sample titles', $results->take(3)->pluck('title')->implode(' · ') ?: '—'],
            ['UpdateSearchIndexJob', class_exists(\App\Jobs\UpdateSearchIndexJob::class) ? 'OK' : 'MISSING'],
            ['simple FTS', 'plainto_tsquery(simple) + limit(20)'],
        ]);

        $hasProduct = $byType->keys()->contains(fn ($t) => str_contains($t, 'Product'));
        $hasLanding = $byType->keys()->contains(fn ($t) => str_contains($t, 'LandingPage'));
        $hasEdu = $byType->keys()->contains(fn ($t) => str_contains($t, 'Education') || $t === 'education');
        $hasBlog = $byType->keys()->contains(fn ($t) => str_contains($t, 'Blog') || $t === 'blog');

        if ($total < 1 || ! $hasProduct || ! $hasLanding || ! $hasEdu || ! $hasBlog) {
            $this->warn('§10 incomplete. Run: php artisan search:verify --reindex');

            return self::FAILURE;
        }

        $this->info('§10 VERIFY PASSED');
        $this->line('Open: /en/search?q='.urlencode($q));

        return self::SUCCESS;
    }
}
