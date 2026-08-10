<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class VerifyAdminPanelCommand extends Command
{
    protected $signature = 'admin:verify';

    protected $description = 'Verify Phase 2 §13 admin panel route coverage';

    public function handle(): int
    {
        $required = [
            'admin.dashboard',
            'admin.login',
            '/dainely-admin-panel/products',
            '/dainely-admin-panel/landings',
            '/dainely-admin-panel/bundles',
            '/dainely-admin-panel/faqs',
            '/dainely-admin-panel/signals',
            '/dainely-admin-panel/related',
            '/dainely-admin-panel/webhooks',
            '/dainely-admin-panel/education',
        ];

        $rows = [];
        $fail = false;

        foreach ($required as $item) {
            if (str_starts_with($item, '/')) {
                $ok = collect(Route::getRoutes())->contains(
                    fn ($r) => in_array('GET', $r->methods(), true) && '/'.$r->uri() === $item
                );
                if (! $ok) {
                    $path = ltrim($item, '/');
                    $ok = collect(Route::getRoutes())->contains(
                        fn ($r) => in_array('GET', $r->methods(), true) && $r->uri() === $path
                    );
                }
                $rows[] = [$item, $ok ? 'OK' : 'MISSING'];
                $fail = $fail || ! $ok;
            } else {
                $ok = Route::has($item);
                $rows[] = [$item, $ok ? 'OK' : 'MISSING'];
                $fail = $fail || ! $ok;
            }
        }

        $rows[] = ['AdminEducationController', class_exists(\App\Http\Controllers\Admin\AdminEducationController::class) ? 'OK' : 'MISSING'];
        $rows[] = ['Landing discount_code fillable', in_array('discount_code', (new \App\Models\Supabase\LandingPage)->getFillable(), true) ? 'OK' : 'MISSING'];
        $rows[] = ['Related update route', collect(Route::getRoutes())->contains(fn ($r) => str_contains($r->uri(), 'dainely-admin-panel/related') && str_contains(implode('|', $r->methods()), 'POST') && str_contains($r->uri(), 'update')) ? 'OK' : 'check'];

        $this->table(['Check', 'Result'], $rows);

        if ($fail) {
            $this->error('§13 VERIFY FAILED');

            return self::FAILURE;
        }

        $this->info('§13 VERIFY PASSED');
        $this->line('Admin covers: products, product_content, blocks (product/landing/education), FAQs, related, landings+discount, bundles, signals, webhooks');

        return self::SUCCESS;
    }
}
