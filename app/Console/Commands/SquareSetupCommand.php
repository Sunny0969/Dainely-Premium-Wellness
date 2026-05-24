<?php

namespace App\Console\Commands;

use App\Services\SquareService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SquareSetupCommand extends Command
{
    protected $signature = 'square:setup';

    protected $description = 'Verify Square credentials and print Location ID for .env';

    public function handle(SquareService $square): int
    {
        $appId = config('square.application_id');
        $token = config('square.access_token');
        $env   = config('square.environment');

        $this->info('Square environment: ' . $env);
        $this->line('Application ID: ' . ($appId ?: '(missing)'));
        $this->line('Access token: ' . ($token ? substr($token, 0, 12) . '...' : '(missing)'));

        if (! $appId || ! $token) {
            $this->error('Set SQUARE_APPLICATION_ID and SQUARE_ACCESS_TOKEN in .env');

            return self::FAILURE;
        }

        $base = $env === 'production'
            ? 'https://connect.squareup.com/v2'
            : 'https://connect.squareupsandbox.com/v2';

        $client = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Square-Version' => '2024-10-17',
        ]);

        if (! config('square.verify_ssl', true)) {
            $client = $client->withoutVerifying();
        }

        $response = $client->get($base . '/locations');

        if (! $response->successful()) {
            $this->error('Square API error: HTTP ' . $response->status());
            $this->line($response->body());
            $this->newLine();
            $this->warn('If you see 401 UNAUTHORIZED, regenerate the sandbox access token in Square Developer Dashboard.');
            $this->line('Dashboard: https://developer.squareup.com/apps → your app → Credentials → Sandbox');

            return self::FAILURE;
        }

        $locations = $response->json('locations') ?? [];
        if ($locations === []) {
            $this->error('No Square locations found for this account.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Available locations:');
        foreach ($locations as $location) {
            $this->line(sprintf(
                '  - %s (%s) [%s]',
                $location['name'] ?? 'Unnamed',
                $location['id'] ?? '?',
                $location['status'] ?? 'unknown'
            ));
        }

        $locationId = $square->getLocationId();
        if ($locationId !== '') {
            $this->newLine();
            $this->info('Add to .env:');
            $this->line('SQUARE_LOCATION_ID=' . $locationId);
        }

        return self::SUCCESS;
    }
}
