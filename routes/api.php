<?php

use App\Http\Controllers\Webhooks\ShopifyWebhookController;
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
