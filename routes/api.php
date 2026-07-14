<?php

use App\Http\Controllers\Webhooks\ShopifyWebhookController;
use App\Http\Controllers\Webhooks\GeneralWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Phase 2 §4.1 — Shopify product synchronisation webhook.
| Full path: POST /api/webhooks/shopify
|
*/

Route::post('/webhooks/shopify', [ShopifyWebhookController::class, 'handle'])
    ->middleware('webhook.shopify')
    ->name('api.webhooks.shopify');

// Phase 2 §12 — Judge.me, Video AI, Wallpass webhook listeners
Route::prefix('webhooks')->group(function () {
    Route::post('/judge', [GeneralWebhookController::class, 'judge'])->name('api.webhooks.judge');
    Route::post('/video-ai', [GeneralWebhookController::class, 'videoAi'])->name('api.webhooks.video-ai');
    Route::post('/wallpass', [GeneralWebhookController::class, 'wallpass'])->name('api.webhooks.wallpass');
});
