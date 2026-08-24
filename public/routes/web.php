<?php

use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\ReviewController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\CartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\EducationController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\BundleController;
use App\Http\Controllers\Frontend\LandingPageController;
use App\Http\Controllers\HealthController;
use App\Services\GeoLocaleService;
use Illuminate\Http\Request;
use App\Http\Controllers\Webhooks\SquareWebhookController;
use App\Http\Controllers\Webhooks\ShopifyWebhookController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminWebhookController;
use App\Http\Controllers\Admin\AdminSignalController;
use App\Http\Controllers\Admin\AdminFaqController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminLandingController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminBundleController;
use App\Http\Controllers\Admin\AdminRelatedController;
use App\Http\Controllers\Admin\AdminEducationController;
use App\Http\Controllers\Admin\AdminShippingController;
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

// Ops health — no secrets. Used to verify pdo_pgsql after hosting changes.
Route::get('/health/supabase', [HealthController::class, 'supabase'])
    ->middleware('throttle:30,1');

// Root redirect — geolocate first-time visitors (VPN / IP), then cookie/session
Route::get('/', function (Request $request) {
    $supported = ['en', 'fr', 'de'];
    $locale = $request->cookie('locale');

    if (! is_string($locale) || ! in_array($locale, $supported, true)) {
        $locale = app(GeoLocaleService::class)->detectLocaleFromRequest($request);
    }

    return redirect()
        ->route('home', ['locale' => $locale])
        ->withCookie(cookie('locale', $locale, 525600));
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
    Route::get('/api/products/{handle}/reviews', [ReviewController::class, 'productReviews'])->name('products.reviews');

    // ── Blog ───────────────────────────────────────────────────
    Route::get('/blog', [BlogController::class, 'index'])->middleware('cf.cache')->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->middleware('cf.cache')->name('blog.show');

    // ── Education Pages ───────────────────────────────────────
    Route::prefix('education')->name('education.')->middleware('cf.cache')->group(function () {
        Route::get('/back-pain',  [EducationController::class, 'backPain'])->name('back-pain');
        Route::get('/sciatica',   [EducationController::class, 'sciatica'])->name('sciatica');
        Route::get('/posture',    [EducationController::class, 'posture'])->name('posture');
        Route::get('/neck-pain',  [EducationController::class, 'neckPain'])->name('neck-pain');
        Route::get('/mobility',   [EducationController::class, 'mobility'])->name('mobility');
        Route::get('/recovery',   [EducationController::class, 'recovery'])->name('recovery');
    });

    // ── Static Pages ──────────────────────────────────────────
    Route::get('/about',          [PageController::class, 'about'])->middleware('cf.cache')->name('about');
    Route::get('/contact',        [PageController::class, 'contact'])->name('contact');
    Route::post('/contact',       [PageController::class, 'contactSubmit'])->name('contact.submit');
    Route::post('/newsletter',    [PageController::class, 'newsletterSubscribe'])->name('newsletter.subscribe');
    Route::get('/faq',            [PageController::class, 'faq'])->middleware('cf.cache')->name('faq');

    // ── Legal Pages ───────────────────────────────────────────
    Route::get('/privacy-policy',  [PageController::class, 'privacy'])->middleware('cf.cache')->name('privacy');
    Route::get('/terms',           [PageController::class, 'terms'])->middleware('cf.cache')->name('terms');
    Route::get('/shipping-policy', [PageController::class, 'shipping'])->middleware('cf.cache')->name('shipping');
    Route::get('/refund-policy',   [PageController::class, 'refund'])->middleware('cf.cache')->name('refund');

    // ── Cart ───────────────────────────────────────────────────
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::get('/api/cart/summary', [CartController::class, 'summary'])->name('cart.summary');

    // ── Checkout ───────────────────────────────────────────────
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/',                    [CheckoutController::class, 'index'])->name('index');
        Route::post('/shopify',            [CheckoutController::class, 'createShopifyCheckout'])->name('shopify');
        Route::post('/process',            [CheckoutController::class, 'process'])->name('process');
        Route::get('/confirmation/{order}',[CheckoutController::class, 'confirmation'])->name('confirmation');
        Route::post('/validate-discount',  [CheckoutController::class, 'validateDiscount'])->name('validate-discount');
        Route::post('/tax-estimate',      [CheckoutController::class, 'estimateTax'])->name('tax-estimate');
    });

    // ── Search ─────────────────────────────────────────────────
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // ── Bundles ────────────────────────────────────────────────
    Route::post('/bundle/{bundleId}/add', [BundleController::class, 'addToCart'])->name('bundle.add');

    // ── Landing offer checkout (product or bundle CTA) ───────
    Route::get('/landing/{id}/checkout', [LandingPageController::class, 'checkout'])
        ->whereNumber('id')
        ->name('landing.checkout');

    // ── LLMs Discovery ─────────────────────────────────────────
    Route::get('/llms.txt', [PageController::class, 'llmsTxt']);

    // ── Catch-All Landing Pages (CMS) ────────────────────────────────
    Route::get('/{slug}', [LandingPageController::class, 'show'])
        ->middleware('cf.cache')
        ->name('landing.show')
        ->where('slug', '[a-z0-9\-]+');
});

// ── Webhooks (no locale prefix, no CSRF — HMAC via middleware) ─────────
// Keep /webhooks/shopify for existing Shopify Admin registrations.
// Docs route also lives at POST /api/webhooks/shopify (routes/api.php).
Route::middleware(['api', 'webhook.shopify'])->prefix('webhooks')->group(function () {
    Route::post('/shopify', [ShopifyWebhookController::class, 'handle'])->name('webhooks.shopify');
});

Route::middleware(['api'])->prefix('webhooks')->group(function () {
    Route::post('/square', [SquareWebhookController::class, 'handle'])->name('webhooks.square');
});

// ── XML Sitemap ─────────────────────────────────────────────────────
// XML sitemaps - one master, one per locale
Route::get('/sitemap.xml',       [PageController::class, 'sitemapIndex'])->middleware('cf.cache')->name('sitemap.index');
Route::get('/{locale}/sitemap.xml', [PageController::class, 'sitemap'])
    ->middleware('cf.cache')
    ->where('locale', 'en|fr|de')
    ->name('sitemap.locale');

// ── Admin CMS Panel (Lightweight CRUD) ──────────────────────────────
Route::prefix('dainely-admin-panel')->group(function () {
    Route::get('/', [AdminAuthController::class, 'entry']);

    // Public Auth Routes
    Route::get('/login', [AdminAuthController::class, 'login'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'authenticate']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Protected Admin Panel Routes
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // Webhooks
        Route::get('/webhooks', [AdminWebhookController::class, 'index']);
        Route::post('/webhooks/process-pending', [AdminWebhookController::class, 'processPending']);
        Route::post('/webhooks/{id}/retry', [AdminWebhookController::class, 'retry']);

        // Knowledge Signals
        Route::get('/signals', [AdminSignalController::class, 'index']);
        Route::post('/signals/{id}/toggle-approval', [AdminSignalController::class, 'toggleApproval']);
        Route::post('/signals/{id}/update', [AdminSignalController::class, 'update']);

        // FAQs
        Route::get('/faqs', [AdminFaqController::class, 'index']);
        Route::get('/faqs/for-target', [AdminFaqController::class, 'forTarget']);
        Route::post('/faqs', [AdminFaqController::class, 'store']);
        Route::post('/faqs/reorder', [AdminFaqController::class, 'reorder']);
        Route::post('/faqs/publish', [AdminFaqController::class, 'publish']);
        Route::post('/faqs/{id}/update', [AdminFaqController::class, 'update']);
        Route::post('/faqs/{id}/delete', [AdminFaqController::class, 'destroy']);

        // Landing Pages
        Route::get('/landings', [AdminLandingController::class, 'index']);
        Route::post('/landings', [AdminLandingController::class, 'store']);
        Route::get('/landings/{id}/edit', [AdminLandingController::class, 'edit']);
        Route::post('/landings/{id}/update', [AdminLandingController::class, 'update']);
        Route::post('/landings/{id}/blocks', [AdminLandingController::class, 'addBlock']);
        Route::post('/landings/{id}/blocks/{blockId}/update', [AdminLandingController::class, 'updateBlock']);
        Route::post('/landings/{id}/blocks/{blockId}/delete', [AdminLandingController::class, 'deleteBlock']);

        // Products Overlay
        Route::get('/products', [AdminProductController::class, 'index']);
        Route::get('/products/{id}/edit', [AdminProductController::class, 'edit']);
        Route::post('/products/{id}/update', [AdminProductController::class, 'update']);
        Route::post('/products/{id}/translate-from-en', [AdminProductController::class, 'translateFromEnglish']);
        Route::post('/products/{id}/unpublish', [AdminProductController::class, 'unpublish']);
        Route::post('/products/{id}/publish', [AdminProductController::class, 'publish']);
        Route::post('/products/{id}/delete', [AdminProductController::class, 'destroy']);
        Route::post('/products/{id}/blocks', [AdminProductController::class, 'addBlock']);
        Route::post('/products/{id}/blocks/{blockId}/update', [AdminProductController::class, 'updateBlock']);
        Route::post('/products/{id}/blocks/{blockId}/delete', [AdminProductController::class, 'deleteBlock']);

        // Bundles
        Route::get('/bundles', [AdminBundleController::class, 'index']);
        Route::post('/bundles', [AdminBundleController::class, 'store']);
        Route::get('/bundles/{id}/edit', [AdminBundleController::class, 'edit']);
        Route::post('/bundles/{id}/update', [AdminBundleController::class, 'update']);
        Route::post('/bundles/{id}/items', [AdminBundleController::class, 'addItem']);
        Route::post('/bundles/{id}/items/{itemId}/delete', [AdminBundleController::class, 'deleteItem']);

        // Internal Knowledge Graph (Relations)
        Route::get('/related', [AdminRelatedController::class, 'index']);
        Route::post('/related', [AdminRelatedController::class, 'store']);
        Route::post('/related/{id}/update', [AdminRelatedController::class, 'update']);
        Route::post('/related/{id}/delete', [AdminRelatedController::class, 'destroy']);

        // Education catalog page_blocks (§13)
        Route::get('/education', [AdminEducationController::class, 'index']);
        Route::get('/education/{id}/edit', [AdminEducationController::class, 'edit']);
        Route::post('/education/{id}/blocks', [AdminEducationController::class, 'addBlock']);
        Route::post('/education/{id}/blocks/{blockId}/update', [AdminEducationController::class, 'updateBlock']);
        Route::post('/education/{id}/blocks/{blockId}/delete', [AdminEducationController::class, 'deleteBlock']);

        // Shipping / free shipping threshold
        Route::get('/shipping', [AdminShippingController::class, 'edit'])->name('admin.shipping');
        Route::post('/shipping', [AdminShippingController::class, 'update']);
    });
});
