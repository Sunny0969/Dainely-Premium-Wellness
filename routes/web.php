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
use App\Services\GeoLocaleService;
use Illuminate\Http\Request;
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

// Root redirect — geolocate first-time visitors (VPN / IP), then cookie/session
Route::get('/', function (Request $request, GeoLocaleService $geo) {
    $supported = ['en', 'fr', 'de'];
    $locale    = $request->cookie('locale');

    if (! is_string($locale) || ! in_array($locale, $supported, true)) {
        $locale = $geo->detectLocaleFromRequest($request);
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

    // ── Cart ───────────────────────────────────────────────────
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');

    // ── Checkout ───────────────────────────────────────────────
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/',                    [CheckoutController::class, 'index'])->name('index');
        Route::post('/process',            [CheckoutController::class, 'process'])->name('process');
        Route::get('/confirmation/{order}',[CheckoutController::class, 'confirmation'])->name('confirmation');
        Route::post('/validate-discount',  [CheckoutController::class, 'validateDiscount'])->name('validate-discount');
        Route::post('/tax-estimate',      [CheckoutController::class, 'estimateTax'])->name('tax-estimate');
    });

    // ── Search ─────────────────────────────────────────────────
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // ── Bundles ────────────────────────────────────────────────
    Route::post('/bundle/{bundleId}/add', [BundleController::class, 'addToCart'])->name('bundle.add');

    // ── LLMs Discovery ─────────────────────────────────────────
    Route::get('/llms.txt', function () {
        $locale = app()->getLocale();
        $path = public_path("llms_{$locale}.txt");
        if (file_exists($path)) {
            return response()->file($path, ['Content-Type' => 'text/plain']);
        }
        abort(404);
    });

    // ── Catch-All Landing Pages ────────────────────────────────
    Route::get('/{slug}', [LandingPageController::class, 'show'])
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
Route::get('/sitemap.xml',       [PageController::class, 'sitemapIndex'])->name('sitemap.index');
Route::get('/{locale}/sitemap.xml', [PageController::class, 'sitemap'])
    ->where('locale', 'en|fr|de')
    ->name('sitemap.locale');

// ── Admin CMS Panel (Lightweight CRUD) ──────────────────────────────
Route::prefix('admin')->group(function () {
    // Public Auth Routes
    Route::get('/login', [AdminAuthController::class, 'login'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'authenticate']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Protected Admin Panel Routes
    Route::middleware(['admin.auth'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

        // Webhooks
        Route::get('/webhooks', [AdminWebhookController::class, 'index']);
        Route::post('/webhooks/{id}/retry', [AdminWebhookController::class, 'retry']);

        // Knowledge Signals
        Route::get('/signals', [AdminSignalController::class, 'index']);
        Route::post('/signals/{id}/toggle-approval', [AdminSignalController::class, 'toggleApproval']);
        Route::post('/signals/{id}/update', [AdminSignalController::class, 'update']);

        // FAQs
        Route::get('/faqs', [AdminFaqController::class, 'index']);
        Route::post('/faqs', [AdminFaqController::class, 'store']);
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
        Route::post('/related/{id}/delete', [AdminRelatedController::class, 'destroy']);
    });
});
