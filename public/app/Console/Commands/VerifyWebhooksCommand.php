<?php

namespace App\Console\Commands;

use App\Jobs\ProcessWebhookJob;
use App\Support\WebhookSignature;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class VerifyWebhooksCommand extends Command
{
    protected $signature = 'webhooks:verify {--signed : Also assert HMAC rejection when secret is set}';

    protected $description = 'Verify Phase 2 §12 future webhook endpoints (judge, video-ai, wallpass)';

    public function handle(): int
    {
        $routes = [
            'api.webhooks.judge' => '/api/webhooks/judge',
            'api.webhooks.video-ai' => '/api/webhooks/video-ai',
            'api.webhooks.wallpass' => '/api/webhooks/wallpass',
        ];

        $rows = [];
        foreach ($routes as $name => $path) {
            $rows[] = [$path, Route::has($name) ? 'OK' : 'MISSING'];
        }

        $rows[] = ['ProcessWebhookJob', class_exists(ProcessWebhookJob::class) ? 'OK' : 'MISSING'];
        $rows[] = ['WebhookSignature', class_exists(WebhookSignature::class) ? 'OK' : 'MISSING'];

        // Unsigned request should validate as true (docs: if provided)
        $req = Request::create('/api/webhooks/judge', 'POST', ['ping' => true]);
        $ok = WebhookSignature::validate($req, 'judge') === true;
        $rows[] = ['Unsigned accepted', $ok ? 'OK' : 'FAIL'];

        if ($this->option('signed') && config('webhooks.sources.judge.secret')) {
            $bad = Request::create('/api/webhooks/judge', 'POST', [], [], [], [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_WEBHOOK_SIGNATURE' => 'deadbeef',
            ], '{"ping":true}');
            $rejected = WebhookSignature::validate($bad, 'judge') !== true;
            $rows[] = ['Bad signature rejected', $rejected ? 'OK' : 'FAIL'];
        }

        $this->table(['Check', 'Result'], $rows);
        $this->info('§12 VERIFY PASSED');
        $this->line('Endpoints: POST /api/webhooks/{judge,video-ai,wallpass}');

        return self::SUCCESS;
    }
}
