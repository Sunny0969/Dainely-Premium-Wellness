<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\EducationController;
use App\Http\Controllers\Webhooks\SquareWebhookController;
use App\Http\Controllers\Webhooks\ShopifyWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dainely Web Routes
|--------------------------------------------------------------------------
|
| Multilingual routes with locale prefix: /en/, /fr/, /de/
| All routes require the {locale} prefix.
|
*/

// Root redirect to default locale
Route::get('/', function () {
    return redirect()->route('home', ['locale' => 'en']);
});

// Multilingual route group
Route::prefix('{locale}')
    ->where(['locale' => 'en|fr|de'])
    ->middleware(['web', 'locale'])
    ->group(function () {

    // ── Homepage ────────────────────────────────────────────────
    Route::get('/', [HomeController::class, 'index'])->name('home');

    // ── Products ───────────────────────────────────────────────
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');

    // ── Blog ───────────────────────────────────────────────────
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

    // ── Education Pages ───────────────────────────────────────
    Route::prefix('education')->name('education.')->group(function () {
        Route::get('/back-pain',  [EducationController::class, 'backPain'])->name('back-pain');
        Route::get('/sciatica',   [EducationController::class, 'sciatica'])->name('sciatica');
        Route::get('/posture',    [EducationController::class, 'posture'])->name('posture');
        Route::get('/neck-pain',  [EducationController::class, 'neckPain'])->name('neck-pain');
        Route::get('/mobility',   [EducationController::class, 'mobility'])->name('mobility');
        Route::get('/recovery',   [EducationController::class, 'recovery'])->name('recovery');
    });

    // ── Static Pages ──────────────────────────────────────────
    Route::get('/about',          [PageController::class, 'about'])->name('about');
    Route::get('/contact',        [PageController::class, 'contact'])->name('contact');
    Route::post('/contact',       [PageController::class, 'contactSubmit'])->name('contact.submit');
    Route::get('/faq',            [PageController::class, 'faq'])->name('faq');

    // ── Legal Pages ───────────────────────────────────────────
    Route::get('/privacy-policy',  [PageController::class, 'privacy'])->name('privacy');
    Route::get('/terms',           [PageController::class, 'terms'])->name('terms');
    Route::get('/shipping-policy', [PageController::class, 'shipping'])->name('shipping');
    Route::get('/refund-policy',   [PageController::class, 'refund'])->name('refund');

    // ── Checkout ───────────────────────────────────────────────
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/',                    [CheckoutController::class, 'index'])->name('index');
        Route::post('/process',            [CheckoutController::class, 'process'])->name('process');
        Route::get('/confirmation/{order}',[CheckoutController::class, 'confirmation'])->name('confirmation');
        Route::post('/validate-discount',  [CheckoutController::class, 'validateDiscount'])->name('validate-discount');
    });
});

// ── Webhooks (no locale prefix, no session middleware) ─────────────────
// These bypass CSRF because they use HMAC signature validation
Route::middleware(['api'])->prefix('webhooks')->group(function () {
    Route::post('/square',  [SquareWebhookController::class, 'handle'])->name('webhooks.square');
    Route::post('/shopify', [ShopifyWebhookController::class, 'handle'])->name('webhooks.shopify');
});

// ── XML Sitemap ─────────────────────────────────────────────────────
// XML sitemaps - one master, one per locale
Route::get('/sitemap.xml',       [PageController::class, 'sitemapIndex'])->name('sitemap.index');
Route::get('/{locale}/sitemap.xml', [PageController::class, 'sitemap'])
    ->where('locale', 'en|fr|de')
    ->name('sitemap.locale');
