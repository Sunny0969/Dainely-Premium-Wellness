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
use App\Http\Controllers\Admin\AdminBlogController;
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

    // ──────────────── Homepage ─────────────────────────────────────────────────────────────
    Route::get('/', [HomeController::class, 'index'])->middleware(['cf.cache', 'isr.cache'])->name('home');

    // ──────────────── Products ─────────────────────────────────────────────────────────────
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/api/products/{handle}/reviews', [ReviewController::class, 'productReviews'])->name('products.reviews');

    // ──────────────── Blog ─────────────────────────────────────────────────────────────
    Route::get('/blog', [BlogController::class, 'index'])->middleware(['cf.cache', 'isr.cache'])->name('blog.index');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->middleware(['cf.cache', 'isr.cache'])->name('blog.show');

    // ──────────────── Education Pages ─────────────────────────────────────────────────────────────
    Route::prefix('education')->name('education.')->middleware(['cf.cache', 'isr.cache'])->group(function () {
        Route::get('/', [EducationController::class, 'index'])->name('index');
        Route::get('/{slug}', [EducationController::class, 'show'])->name('show');
    });

    // ──────────────── Static Pages ─────────────────────────────────────────────────────────────
    Route::get('/about',          [PageController::class, 'about'])->middleware(['cf.cache', 'isr.cache'])->name('about');
    Route::get('/contact',        [PageController::class, 'contact'])->name('contact');
    Route::post('/contact',       [PageController::class, 'contactSubmit'])->name('contact.submit');
    Route::post('/newsletter',    [PageController::class, 'newsletterSubscribe'])->name('newsletter.subscribe');
    Route::get('/faq',            [PageController::class, 'faq'])->middleware(['cf.cache', 'isr.cache'])->name('faq');

    // ──────────────── Legal Pages ─────────────────────────────────────────────────────────────
    Route::get('/privacy-policy',  [PageController::class, 'privacy'])->middleware(['cf.cache', 'isr.cache'])->name('privacy');
    Route::get('/terms',           [PageController::class, 'terms'])->middleware(['cf.cache', 'isr.cache'])->name('terms');
    Route::get('/shipping-policy', [PageController::class, 'shipping'])->middleware(['cf.cache', 'isr.cache'])->name('shipping');
    Route::get('/refund-policy',   [PageController::class, 'refund'])->middleware(['cf.cache', 'isr.cache'])->name('refund');

    // ──────────────── Cart ─────────────────────────────────────────────────────────────
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'store'])->name('cart.store');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::get('/api/cart/summary', [CartController::class, 'summary'])->name('cart.summary');

    // ──────────────── Checkout ─────────────────────────────────────────────────────────────
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/',                    [CheckoutController::class, 'index'])->name('index');
        Route::post('/shopify',            [CheckoutController::class, 'createShopifyCheckout'])->name('shopify');
        Route::post('/process',            [CheckoutController::class, 'process'])->name('process');
        Route::get('/confirmation/{order}',[CheckoutController::class, 'confirmation'])->name('confirmation');
        Route::post('/validate-discount',  [CheckoutController::class, 'validateDiscount'])->name('validate-discount');
        Route::post('/tax-estimate',      [CheckoutController::class, 'estimateTax'])->name('tax-estimate');
    });

    // ──────────────── Search ─────────────────────────────────────────────────────────────
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // ──────────────── Bundles ─────────────────────────────────────────────────────────────
    Route::post('/bundle/{bundleId}/add', [BundleController::class, 'addToCart'])->name('bundle.add');

    // ──────────────── Landing offer checkout (product or bundle CTA) ────────────────
    Route::get('/landing/{id}/checkout', [LandingPageController::class, 'checkout'])
        ->whereNumber('id')
        ->name('landing.checkout');

    // ──────────────── LLMs Discovery ─────────────────────────────────────────────────────────────
    Route::get('/llms.txt', [PageController::class, 'llmsTxt']);

    // ──────────────── Catch-All Landing Pages (CMS) ──────────────────────────────────────────────
    Route::get('/{slug}', [LandingPageController::class, 'show'])
        ->middleware(['cf.cache', 'isr.cache'])
        ->name('landing.show')
        ->where('slug', '[a-z0-9\-]+');
});

// ──────────────── Webhooks (no locale prefix, no CSRF — HMAC via middleware) ────────
Route::middleware(['api', 'webhook.shopify'])->prefix('webhooks')->group(function () {
    Route::post('/shopify', [ShopifyWebhookController::class, 'handle'])->name('webhooks.shopify');
});

Route::middleware(['api'])->prefix('webhooks')->group(function () {
    Route::post('/square', [SquareWebhookController::class, 'handle'])->name('webhooks.square');
});

// ──────────────── S3 Storage Media Proxy / Streamer ──────────────────────────
Route::get('/storage/s3/{path}', function ($path) {
    $disk = \Illuminate\Support\Facades\Storage::disk('s3');
    if (! $disk->exists($path)) {
        abort(404);
    }
    $mime = $disk->mimeType($path) ?: 'application/octet-stream';
    $stream = $disk->readStream($path);
    return response()->stream(function () use ($stream) {
        fpassthru($stream);
        if (is_resource($stream)) {
            fclose($stream);
        }
    }, 200, [
        'Content-Type' => $mime,
        'Cache-Control' => 'public, max-age=31536000, immutable',
    ]);
})->where('path', '.*')->name('s3.media');

// ──────────────── XML Sitemap ─────────────────────────────────────────────────────────────
Route::get('/sitemap.xml', function() {
    return response(file_get_contents(base_path('public/sitemap.xml')), 200, [
        'Content-Type' => 'text/xml'
    ]);
});

// ──────────────── Admin CMS Panel (Lightweight CRUD) ──────────────────────────────
Route::prefix('dainely-admin-panel')->group(function () {
    Route::get('/', [AdminAuthController::class, 'entry']);

    // Public Auth Routes
    Route::get('/login', [AdminAuthController::class, 'login'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'authenticate']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Protected Admin Panel Routes
Route::middleware(['admin.auth', \App\Http\Middleware\ClearCacheOnAdminSave::class])->group(function () {
Route::get('/profile', [AdminAuthController::class, 'profile'])->name('admin.profile');
        Route::post('/profile', [AdminAuthController::class, 'updateProfile']);
        Route::post('/editor-upload', [\App\Http\Controllers\Admin\AdminMediaController::class, 'uploadEditorImage']);
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
        Route::post('/landings/{id}/delete', [AdminLandingController::class, 'delete']);

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

        // Education Pages CRUD
        Route::get('/education', [AdminEducationController::class, 'index']);
        Route::post('/education/sync-all', [AdminEducationController::class, 'syncAll']);
        Route::get('/education/sync-all', [AdminEducationController::class, 'syncAll']);
        Route::get('/education/create', [AdminEducationController::class, 'create']);
        Route::post('/education', [AdminEducationController::class, 'store']);
        Route::get('/education/{id}/edit', [AdminEducationController::class, 'edit']);
        Route::put('/education/{id}', [AdminEducationController::class, 'update']);
        Route::delete('/education/{id}', [AdminEducationController::class, 'destroy']);

        // Blogs CRUD
        Route::get('/blogs', [AdminBlogController::class, 'index']);
        Route::get('/blogs/create', [AdminBlogController::class, 'create']);
        Route::post('/blogs', [AdminBlogController::class, 'store']);
        Route::get('/blogs/{id}/edit', [AdminBlogController::class, 'edit']);
        Route::post('/blogs/{id}/update', [AdminBlogController::class, 'update']);
        Route::post('/blogs/{id}/delete', [AdminBlogController::class, 'destroy']);

        // Shipping / free shipping threshold
        Route::get('/shipping', [AdminShippingController::class, 'edit'])->name('admin.shipping');
        Route::post('/shipping', [AdminShippingController::class, 'update']);
    });
});

Route::get('/clear-live-cache-now', function () {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    return '<h1>Cache Cleared Successfully!</h1><p>Please go back and refresh your live website.</p>';
});

Route::get('/sync-education-translations', function () {
    try {
        $controller = app(\App\Http\Controllers\Admin\AdminEducationController::class);
        $englishPages = \App\Models\Catalog\EducationPage::where('is_active', true)
            ->where(function ($q) {
                $q->where('locale', 'en')
                  ->orWhereNull('locale')
                  ->orWhere('locale', '');
            })
            ->get();

        $count = 0;
        foreach ($englishPages as $page) {
            $controller->syncTranslations($page);
            $count++;
        }

        \Illuminate\Support\Facades\Cache::forget('education_index_pages_v1_en');
        \Illuminate\Support\Facades\Cache::forget('education_index_pages_v1_fr');
        \Illuminate\Support\Facades\Cache::forget('education_index_pages_v1_de');
        \Illuminate\Support\Facades\Cache::forget('nav_edu_desktop_v2_en');
        \Illuminate\Support\Facades\Cache::forget('nav_edu_desktop_v2_fr');
        \Illuminate\Support\Facades\Cache::forget('nav_edu_desktop_v2_de');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        return "<h1>All {$count} Education Pages Translated & Synced to FR & DE!</h1><p>Cache cleared successfully. Storefront is now 100% in target language.</p>";
    } catch (\Exception $e) {
        return "<h1>Error syncing translations:</h1><p>" . $e->getMessage() . "</p>";
    }
});

Route::get('/apply-education-58-update', function () {
    try {
        $page = \App\Models\Catalog\EducationPage::find(58);
        if (!$page) {
            $page = \App\Models\Catalog\EducationPage::where('slug', 'pickleball-injury-prevention')->first();
        }

        if (!$page) {
            $page = new \App\Models\Catalog\EducationPage();
        }

        $page->title = 'Pickleball Injury Prevention: 10 Smart Ways to Play Safer After 50';
        $page->slug = 'pickleball-injury-prevention';
        $page->category = 'Active 50+ Lifestyle';
        $page->is_active = true;
        $page->locale = 'en';

        // SEO
        $page->meta_title = 'Pickleball Injury Prevention: 10 Smart Ways to Play Safer After 50 | Dainely';
        $page->meta_description = 'Learn practical, evidence-based ways to reduce injury risks in pickleball after 50. Expert guidance on warm-ups, strength, balance, footwear, and recovery.';
        $page->canonical_url = 'https://dainely.com/en/education/pickleball-injury-prevention';

        // Hero
        $page->hero_title = 'Play Smart. Move Ready. Keep Playing.';
        $page->hero_description = '<div style="max-width: 750px; margin: 20px auto; padding: 24px; background-color: #f8fafc; border-left: 5px solid #1e3a5f; border-radius: 8px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;">
<p style="margin: 0 0 16px 0; font-size: 17px; line-height: 1.7; color: #1e293b;">Pickleball is a fun, social way to stay active as you age—but the quick stops, starts, lunges, reaches, and directional changes place genuine physical demands on your body.</p>
<p style="margin: 0; font-size: 15px; line-height: 1.6; color: #475569;">A few simple habits, from warming up and building strength and balance to choosing appropriate footwear and managing your playing load, can help you reduce preventable risks and stay ready for the game you enjoy.</p>
</div>';
        $page->hero_image = 'https://media.dainely.com/dainely-media/optimized/education/W3YURv5Vqaaq97pgPUmQNcEDpzbKgEzhviYNmlsl.webp';
        $page->author_name = 'Dr. Amanda Foster';
        $page->author_role = 'Physical Therapy & Sports Rehabilitation Consultant';
        $page->read_time = '8 min read';
        $page->author_image = 'https://images.unsplash.com/photo-1594824813628-98402a7b822d?auto=format&fit=crop&w=200&q=80';

        // Figures
        $page->figures = [
            [
                'value' => '5–10 MINUTES',
                'label' => '<h3><strong>Dynamic Warm-Up</strong></h3><p>A short, progressive warm-up can help you transition from rest into the multidirectional movements used during pickleball.</p>'
            ],
            [
                'value' => '2 DAYS',
                'label' => '<h3><strong>Strength Training</strong></h3><p>Adults 65+ are advised to include muscle-strengthening activities on at least 2 days each week as part of their overall physical activity routine.</p>'
            ],
            [
                'value' => 'BALANCE',
                'label' => '<h3><strong>Practice Regularly</strong></h3><p>For adults 65+, the CDC recommends including balance activities as part of weekly physical activity. Balance, strength, and aerobic activity form an important combination for maintaining physical function.</p>'
            ]
        ];

        // Root Causes (10 items from Word file)
        $page->root_causes_title = 'Why Pickleball Injuries Happen—and What You Can Control';
        $page->root_causes = [
            [
                'title' => '1. Rapid Direction Changes',
                'description' => '<p>Pickleball frequently requires you to move sideways, forward, backward, and diagonally. A sudden change of direction can challenge your feet, ankles, knees, hips, balance, and ability to control your body.</p><h4><strong>What You Can Do</strong></h4><p>Practice controlled lateral movement, weight shifting, step-and-hold exercises, and lower-body strengthening away from the court. Start with movements you can control rather than trying to immediately match the speed of experienced players.</p>'
            ],
            [
                'title' => '2. Falls and Loss of Balance',
                'description' => '<p>Falls are the leading injury mechanism identified in the latest pickleball-specific systematic review. A player may lose balance while lunging, reaching, changing direction, stepping backward, or trying to recover quickly.</p><h4><strong>What You Can Do</strong></h4><p>Integrate balance work into your regular training routine:</p><ul><li>Supported single-leg standing</li><li>Controlled weight shifts</li><li>Step-and-hold exercises</li><li>Heel-to-toe walking</li></ul><div style="background-color: #f0fdf4; padding: 12px 16px; border-radius: 8px; border-left: 4px solid #16a34a; margin-top: 10px;"><p style="margin: 0; color: #166534; font-size: 14px;">💡 <em>Keep a sturdy support nearby when practicing challenging balance exercises.</em></p></div>'
            ],
            [
                'title' => '3. Doing Too Much Too Soon',
                'description' => '<p>Pickleball is enjoyable, which can make it easy to increase your playing time quickly. But your enthusiasm can increase faster than your body\'s ability to adapt. Going from occasional play to multiple long sessions in a short period can place a very different demand on your muscles, tendons, joints, and overall recovery capacity.</p><h4><strong>What You Can Do</strong></h4><p>Gradually increase your playing frequency, duration, and intensity—especially after a long break. If you change one part of your routine, consider keeping the other variables relatively stable while your body adapts.</p>'
            ],
            [
                'title' => '4. Starting Cold',
                'description' => '<p>Going directly from sitting in a car or at home to rapid court movement gives your body little time to transition into activity.</p><h4><strong>What You Can Do</strong></h4><p>Give yourself approximately 5–10 minutes to gradually increase your movement before your first game. Start easy and progressively introduce the movements you\'ll use on the court.</p>'
            ],
            [
                'title' => '5. Insufficient Strength and Conditioning',
                'description' => '<p>Pickleball involves repeated pushing, stopping, bending, stepping, reaching, and recovering. Your body doesn\'t need to be exceptionally strong—but it benefits from having an adequate physical foundation for those demands.</p><h4><strong>What You Can Do</strong></h4><p>Complement pickleball with regular strength training. Useful movements include:</p><ul><li>Sit-to-stands</li><li>Step-ups</li><li>Calf raises</li><li>Supported hip hinges</li><li>Resistance-band rows</li><li>Controlled lateral steps</li></ul><p>For adults 65+, the CDC recommends muscle-strengthening activity on at least 2 days each week as part of a broader physical activity routine.</p>'
            ],
            [
                'title' => '6. Limited Balance and Body Control',
                'description' => '<p>Balance is more than standing on one foot. During pickleball, you need to control your body while your weight shifts, your feet reposition, and your upper body reaches toward the ball.</p><h4><strong>What You Can Do</strong></h4><p>Practice balance when you\'re not playing. Start with simple supported exercises and gradually make them more challenging as your control improves.</p>'
            ],
            [
                'title' => '7. Inappropriate Footwear',
                'description' => '<p>Pickleball includes substantial side-to-side movement. Footwear designed primarily for straight-line running may not provide the same type of support or stability you want for court movement. USA Pickleball recommends comfortable court shoes and specifically notes the importance of footwear suited to pickleball\'s lateral movement.</p><h4><strong>What You Can Do</strong></h4><p>Choose court shoes that fit comfortably, provide appropriate traction, match your indoor/outdoor playing surface, are in good condition, and allow your toes room without foot sliding.</p>'
            ],
            [
                'title' => '8. Court Hazards and Poor Court Awareness',
                'description' => '<p>Good physical preparation cannot compensate for a wet surface, debris, equipment left near the court, or an unexpected collision.</p><h4><strong>What You Can Do</strong></h4><p>Before playing: check the court surface for moisture/debris, keep loose equipment out of the playing area, know where partner and opponents are, be aware of neighboring courts, and avoid moving backward without knowing what\'s behind you.</p>'
            ],
            [
                'title' => '9. Playing Through Fatigue',
                'description' => '<p>Your movement can change when you\'re tired. Steps may become less precise. Reaction time may feel slower. You may reach farther instead of repositioning your feet. That\'s when a movement that normally feels easy can become harder to control.</p><h4><strong>What You Can Do</strong></h4><p>Pay attention to how you\'re moving, not just how much longer you can keep playing. Taking a break is part of smart training—not a sign that you\'re giving up.</p>'
            ],
            [
                'title' => '10. Ignoring a New or Worsening Problem',
                'description' => '<p>There is a difference between normal effort and a symptom that is getting worse. Trying to play through significant pain, swelling, instability, dizziness, or another concerning symptom isn\'t a good strategy.</p><h4><strong>What You Can Do</strong></h4><p>Stop and assess what\'s happening. If symptoms persist, worsen, or interfere with normal activities, seek appropriate professional guidance before returning to play.</p>'
            ]
        ];

        // Treatments & Evidence-based strategies (Action points from Word file)
        $page->treatments_title = 'Evidence-Based Strategies & Practical Action Points';
        $page->treatments_description = '<p>A 2026 systematic review of 15 studies examining lower-extremity injuries in adult pickleball players found that sprains, strains, and fractures were among the most commonly reported injuries. Here are the core actions to protect your body:</p>';
        $page->treatments = [
            ['text' => '<strong>1. Dedicated Warm-Up:</strong> Spend 5–10 minutes ramping up with dynamic walking, arm circles, hip rotations, and mini squats before starting your first game.'],
            ['text' => '<strong>2. Build Foundation Strength:</strong> Regular bodyweight and resistance exercises on at least 2 days per week protect tendons, ligaments, and knee joints.'],
            ['text' => '<strong>3. Progressive Loading:</strong> Avoid sudden spikes in playtime. Increase frequency or intensity gradually over several weeks.'],
            ['text' => '<strong>4. Lateral Court Footwear:</strong> Wear dedicated court shoes with side-to-side support rather than straight-line running shoes.'],
            ['text' => '<strong>5. Respect Fatigue & Rest:</strong> Take breaks between games and schedule rest days to allow muscle and joint tissue to recover fully.']
        ];

        // Content Blocks (Comprehensive strategies, Video embed, Checklist, Products, Disclaimer)
        $page->content_blocks = [
            [
                'title' => 'Video Demonstration: Pickleball Injury Prevention',
                'content' => '<div style="max-width: 100%; margin: 20px 0;"><div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);"><iframe src="https://www.youtube.com/embed/sNCBUGDUgfk" title="Pickleball Injury Prevention Video" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="position: absolute; top:0; left: 0; width: 100%; height: 100%;"></iframe></div><p style="margin-top: 10px; font-size: 14px; color: #64748b; text-align: center;"><em>Watch our complete video guide for visual movement demonstrations and court safety tips.</em></p></div>'
            ],
            [
                'title' => '1. Warm Up for the Movements You\'re About to Make',
                'content' => '<p>A good pickleball warm-up doesn\'t need to be complicated. Start with easy movement and gradually progress toward the movements you\'ll use on the court.</p><h3><strong>A simple 5–10 minute progression:</strong></h3><ul><li><strong>1–2 minutes:</strong> Easy walking or marching</li><li><strong>1–2 minutes:</strong> Shoulder circles, arm swings, and gentle trunk movements</li><li><strong>1–2 minutes:</strong> Hip circles, leg swings, and mini squats</li><li><strong>1–2 minutes:</strong> Controlled side steps and weight shifts</li><li><strong>1–2 minutes:</strong> Gradually faster pickleball-specific movements</li></ul><p>The goal is to <strong>ramp up—not wear yourself out.</strong></p><p>For the complete routine, see our <a href="/en/education/pickleball-warm-up-routine" style="color: #2563eb; text-decoration: underline; font-weight: 600;">Pickleball Warm-Up Exercises</a> guide.</p>'
            ],
            [
                'title' => '2. Build the Physical Qualities Pickleball Demands',
                'content' => '<p>Playing pickleball is valuable, but you don\'t have to expect the sport itself to train every physical quality. Think about what the game asks you to do:</p><ul><li><strong>Strength:</strong> Push, stop, bend, and recover.</li><li><strong>Balance:</strong> Control your body while your weight shifts.</li><li><strong>Mobility:</strong> Reach comfortable positions.</li><li><strong>Coordination:</strong> Combine those movements smoothly.</li><li><strong>Endurance:</strong> Continue moving as the game progresses.</li></ul><p>Training these qualities outside the court can complement your time playing.</p>'
            ],
            [
                'title' => '3. Injury Prevention Isn\'t About Avoiding Movement',
                'content' => '<p>It can be tempting to think that staying safe means avoiding bending, lunging, rotation, or fast movement. That\'s not the goal.</p><p>Your body benefits from being exposed to movement and physical challenge in an appropriate, progressive way. Instead of avoiding every challenging movement, work toward being able to perform the movements you need <strong>comfortably, progressively, and with control.</strong></p>'
            ],
            [
                'title' => '4. Don\'t Confuse Flexibility With Readiness',
                'content' => '<p>Being flexible doesn\'t automatically mean you\'re ready to play. Before pickleball, your body needs to transition from rest into movement. That\'s why dynamic movement is generally a better fit at the beginning of a session than spending a long time holding static stretches.</p><p>Save longer, comfortable flexibility work for after play or a separate mobility session.</p><p>For more information, see our <a href="/en/education/pickleball-stretches" style="color: #2563eb; text-decoration: underline; font-weight: 600;">Pickleball Stretches</a> guide.</p>'
            ],
            [
                'title' => '5. Increase Your Playing Load Gradually',
                'content' => '<p>One of the easiest ways to overdo pickleball is to increase several variables at the same time. For example: you normally play once a week, then you suddenly play four days in one week with several games each day. Your motivation may be ready; your body may not be.</p><p>Instead, when possible, change one variable at a time (frequency, duration, intensity) and give your body time to adapt.</p>'
            ],
            [
                'title' => 'A Simple Pickleball Safety Checklist',
                'content' => '<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin: 20px 0;"><h4 style="margin-top: 0; color: #0f172a; font-weight: bold;">Before you step onto the court, ask yourself:</h4><ul style="list-style-type: none; padding-left: 0; margin-bottom: 0;"><li style="padding: 6px 0; border-bottom: 1px solid #f1f5f9;"><strong style="color: #1e3a5f;">BODY:</strong> Am I reasonably recovered and ready to play?</li><li style="padding: 6px 0; border-bottom: 1px solid #f1f5f9;"><strong style="color: #1e3a5f;">WARM-UP:</strong> Have I gradually warmed up?</li><li style="padding: 6px 0; border-bottom: 1px solid #f1f5f9;"><strong style="color: #1e3a5f;">SHOES:</strong> Am I wearing appropriate court footwear?</li><li style="padding: 6px 0; border-bottom: 1px solid #f1f5f9;"><strong style="color: #1e3a5f;">COURT:</strong> Is the playing surface clear and suitable?</li><li style="padding: 6px 0; border-bottom: 1px solid #f1f5f9;"><strong style="color: #1e3a5f;">AWARENESS:</strong> Do I know where my partner, opponents, and nearby players are?</li><li style="padding: 6px 0; border-bottom: 1px solid #f1f5f9;"><strong style="color: #1e3a5f;">PACE:</strong> Am I playing at an intensity appropriate for my current fitness?</li><li style="padding: 6px 0;"><strong style="color: #1e3a5f;">RECOVERY:</strong> Have I given myself enough recovery between sessions?</li></ul></div>'
            ],
            [
                'title' => 'Dainely: Support for Active Movement',
                'content' => '<p><strong>Dainely Belt — Support for Active Movement:</strong> Designed for active adults who want comfortable support while moving through everyday activities, recreation, pickleball, golf, walking, travel, and more. <a href="/en/products/dainely-belt" style="color: #2563eb; text-decoration: underline; font-weight: bold;">Explore Dainely Belt &rarr;</a></p><p><strong>Daily Support & Recovery System:</strong> Combines the Dainely Belt and Extender with practical movement preparation and recovery routines designed around active adults. <a href="/en/products/daily-support-recovery-system" style="color: #2563eb; text-decoration: underline; font-weight: bold;">Explore Support & Recovery System &rarr;</a></p><p><strong>Ergo Cushion:</strong> Comfort between games, during travel, or relaxing after a day on the court. <a href="/en/products/ergocushion" style="color: #2563eb; text-decoration: underline; font-weight: bold;">Explore Ergo Cushion &rarr;</a></p>'
            ],
            [
                'title' => 'Medical Disclaimer',
                'content' => '<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 16px; border-radius: 6px; font-size: 14px; color: #7f1d1d; line-height: 1.6;"><p style="margin: 0 0 10px 0;"><em>This article is provided for general educational and wellness purposes only. It is not intended to diagnose, treat, cure, or prevent any disease, medical condition, or injury.</em></p><p style="margin: 0 0 10px 0;"><em>No exercise, warm-up, product, footwear choice, or safety strategy can guarantee that an injury will not occur. Exercise selection, playing frequency, and intensity should be appropriate for your individual fitness level and abilities.</em></p><p style="margin: 0;"><em>Stop playing or exercising if you experience sharp or worsening pain, significant swelling, instability, dizziness, or other concerning symptoms. Consult a physician or physical therapist for tailored medical advice.</em></p></div>'
            ]
        ];

        $page->layout_order = ['figures', 'root_causes', 'content_blocks', 'treatments'];
        $page->save();

        // Sync FR and DE translations automatically
        $controller = app(\App\Http\Controllers\Admin\AdminEducationController::class);
        $controller->syncTranslations($page);

        \App\Support\StorefrontCache::forgetEducation($page->id);
        \Illuminate\Support\Facades\Cache::forget('education_index_pages_v1_en');
        \Illuminate\Support\Facades\Cache::forget('education_index_pages_v1_fr');
        \Illuminate\Support\Facades\Cache::forget('education_index_pages_v1_de');
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        return "<h1>Education Page 58 Successfully Updated & Synced!</h1><p>Title: " . htmlspecialchars($page->title) . "</p><p>Hero, Figures, 10 Root Causes, Video Embeds, Strategies, Checklist, and FR/DE Translations are all 100% updated.</p><p><a href='/en/education/pickleball-injury-prevention'>View Live Page</a></p>";
    } catch (\Exception $e) {
        return "<h1>Error updating page 58:</h1><p>" . $e->getMessage() . "</p>";
    }
});

Route::get('/setup-education-db-now', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'EducationPagesSeeder', '--force' => true]);
        return '<h1>Database Migrated & Seeded Successfully!</h1><p>The Education Pages tables have been set up. You can now use the admin panel.</p>';
    } catch (\Exception $e) {
        return '<h1>Error setting up database:</h1><p>' . $e->getMessage() . '</p>';
    }
});

Route::get('/sync-shopify-now', function () {
    \Illuminate\Support\Facades\Artisan::call('shopify:sync-catalog');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    return '<h1>Shopify Images Synced & Cache Cleared!</h1><p>Please go back and refresh your live website.</p>';
});


Route::get('/sync-shopify-debug', function () {
    \Illuminate\Support\Facades\Artisan::call('shopify:sync-catalog');
    $output = \Illuminate\Support\Facades\Artisan::output();
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    return '<pre>'.htmlentities($output).'</pre><h1>Done!</h1>';
});


Route::get('/sync-shopify-debug2', function () {
    return response()->json([
        'shop_domain' => config('shopify.shop_domain'),
        'storefront_domain' => config('shopify.storefront_domain'),
        'has_admin_token' => !empty(config('shopify.admin_access_token')),
        'has_storefront_token' => !empty(config('shopify.storefront_access_token')),
        'has_client_id' => !empty(config('shopify.client_id')),
    ]);
});



Route::get("/sync-shopify-debug3", function () {
    $shopify = app(\App\Services\ShopifyService::class);
    $result = $shopify->fetchProductByHandle("brace", null, true);
    return response()->json([
        "success" => $result["success"] ?? false,
        "error" => $result["error"] ?? null,
        "image_count" => isset($result["product"]["images"]) ? count($result["product"]["images"]) : 0,
        "product" => $result["product"] ?? null,
    ]);
});


Route::get('/clear-brace-cache', function () {
    \Illuminate\Support\Facades\Cache::forget('shopify_product_handle_e228d440076a08f51df1c7d2466f2ed6'); // md5('brace')
    \Illuminate\Support\Facades\Cache::forget('shopify_product_handle_brace');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return 'Cache cleared for brace! Now visit /en/products/brace';
});




Route::get("/fix-german-issue", function () {
    $fixedBlocks = 0;
    if (class_exists(\App\Models\Supabase\PageBlock::class)) {
        $blocks = \App\Models\Supabase\PageBlock::where("locale", "en")
            ->where("title", "like", "%Medizinischer%")
            ->get();
        foreach ($blocks as $block) {
            $block->title = "Medical Disclaimer";
            $block->content = "<p><em>The information provided on this site is for general educational and informational purposes only. It is not intended to diagnose, treat, cure, or prevent any disease, condition, or injury, and it should not be considered medical advice.</em></p><p><em>Physical activity and exercise needs vary from person to person. Consult a qualified healthcare professional if you have a medical condition, recent injury, significant mobility or balance concerns, or questions about the appropriate type or amount of activity for you.</em></p><p><em>Dainely products are designed for general support and comfort and are not intended to replace professional medical evaluation, diagnosis, or treatment.</em></p>";
            $block->save();
            $fixedBlocks++;
        }
    }
    
    $fixedPages = 0;
    if (class_exists(\App\Models\Catalog\EducationPage::class)) {
        $pages = \App\Models\Catalog\EducationPage::where("locale", "en")->get();
        foreach ($pages as $page) {
            $needsSave = false;
            if ($page->content_blocks) {
                $cb = $page->content_blocks;
                foreach ($cb as &$block) {
                    if (isset($block["title"]) && str_contains($block["title"], "Medizinischer")) {
                        $block["title"] = "Medical Disclaimer";
                        $block["content"] = "<p><em>The information provided on this site is for general educational and informational purposes only. It is not intended to diagnose, treat, cure, or prevent any disease, condition, or injury, and it should not be considered medical advice.</em></p><p><em>Physical activity and exercise needs vary from person to person. Consult a qualified healthcare professional if you have a medical condition, recent injury, significant mobility or balance concerns, or questions about the appropriate type or amount of activity for you.</em></p><p><em>Dainely products are designed for general support and comfort and are not intended to replace professional medical evaluation, diagnosis, or treatment.</em></p>";
                        $needsSave = true;
                    }
                }
                if ($needsSave) {
                    $page->content_blocks = $cb;
                    $page->save();
                    $fixedPages++;
                }
            }
        }
    }

    \Illuminate\Support\Facades\Artisan::call("cache:clear");
    \Illuminate\Support\Facades\Artisan::call("view:clear");
    return "Fixed $fixedBlocks page blocks and $fixedPages education pages. Cache cleared. Please refresh the page.";
});


Route::get('/fix-recovery-seo', function () {
    \App\Models\Catalog\EducationPage::where('slug', 'recovery-relaxation')->update([
        'meta_title' => 'Recovery and Relaxation Guide for Active Lifestyles | Dainely',
        'meta_description' => 'Learn practical recovery and relaxation habits covering sleep, movement, hydration, nutrition and rest to help build a sustainable active lifestyle routine.'
    ]);
    \Illuminate\Support\Facades\Cache::flush();
    return 'SEO updated for recovery-relaxation! Now visit /en/education/recovery-relaxation';
});


Route::get('/fix-bom', function () {
    $file = app_path('Http/Controllers/Frontend/CartController.php');
    $content = file_get_contents($file);
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
    $content = ltrim($content);
    file_put_contents($file, $content);
    return 'BOM and spaces removed from CartController.php! You can now test Add to Cart.';
});

Route::get('/fix-all-boms', function () {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path()));
    $count = 0;
    foreach ($it as $file) {
        if ($file->getExtension() === 'php') {
            $path = $file->getPathname();
            if (str_contains($path, 'vendor')) continue;
            $c = file_get_contents($path);
            if (str_starts_with($c, "\xEF\xBB\xBF")) {
                file_put_contents($path, substr($c, 3));
                $count++;
            }
        }
    }
    return 'BOMs removed from ' . $count . ' files! Cart JSON responses should now work properly.' ;
});

Route::get('/fix-s3-images', function () {
    try {
        $files = \Illuminate\Support\Facades\Storage::disk('s3')->files('optimized/editor');
        $count = 0;
        foreach ($files as $file) {
            \Illuminate\Support\Facades\Storage::disk('s3')->setVisibility($file, 'public');
            $count++;
        }
        return 'Successfully made ' . $count . ' images public!';
    } catch (\Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
});

Route::get('/run-composer', function () {
    $output = shell_exec('composer require league/flysystem-aws-s3-v3 2>&1');
    return '<pre>' . $output . '</pre>';
});

Route::get("/clear-cache", function () {
    $out1 = shell_exec("composer dump-autoload 2>&1");
    \Illuminate\Support\Facades\Artisan::call("optimize:clear");
    return "<pre>" . $out1 . "\nOptimize Clear:\n" . \Illuminate\Support\Facades\Artisan::output() . "</pre>";
});

Route::get("/run-composer-fix", function () {
    chdir(base_path());
    $out = shell_exec("composer require league/flysystem-aws-s3-v3 2>&1");
    return "<pre>" . $out . "</pre>";
});

Route::get("/fix-blog-images", function () {
    $postTranslation = App\Models\BlogPostTranslation::where("slug", "why-mobility-matters-after-50-7-simple-ways-to-keep-moving-with-confidence")->first();
    if ($postTranslation) {
        $content = $postTranslation->content;
        $content = preg_replace("/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/1789759755-[^\.]+\.jpg/", "/images/women-walking.jpg", $content);
        $content = preg_replace("/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/1789759756-[^\.]+\.jpg/", "/images/lifestyle-everyday-movement.webp", $content);
        $content = preg_replace("/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/1789759757-[^\.]+\.jpg/", "/images/lifestyle-dainely-in-motion.png", $content);
        $content = preg_replace("/https:\/\/media\.dainely\.com\/dainely-media\/optimized\/editor\/[^\"]+?\.jpg/", "/images/hero-lifestyle.png", $content);
        $postTranslation->content = $content;
        $postTranslation->save();
        return "Images replaced successfully!";
    }
    return "Post not found.";
});

Route::get("/flush-all-cache", function() { \Illuminate\Support\Facades\Cache::flush(); return "Cache flushed completely!"; });

Route::get("/migrate-old-images", function () {
    $log = [];
    try {
        // 1. Migrate Blogs
        $blogs = App\Models\BlogPostTranslation::all();
        foreach ($blogs as $blog) {
            $changed = false;
            $html = $blog->content;
            
            // Find all optimized/editor images in blog content
            if (preg_match_all("/\/optimized\/editor\/([a-zA-Z0-9_.-]+)/", $html, $matches)) {
                foreach ($matches[1] as $filename) {
                    $oldPath = "optimized/editor/" . $filename;
                    $newPath = "dainely/media/blogs/" . $filename;
                    if (Storage::disk("s3")->exists($oldPath)) {
                        Storage::disk("s3")->copy($oldPath, $newPath);
                        $html = str_replace("optimized/editor/" . $filename, "dainely/media/blogs/" . $filename, $html);
                        $changed = true;
                        $log[] = "Copied $oldPath to $newPath";
                    }
                }
            }
            // Find all images/ in blog content
            if (preg_match_all("/\/images\/([a-zA-Z0-9_.-]+)/", $html, $matches)) {
                foreach ($matches[1] as $filename) {
                    // Avoid matching /images/ (local) vs S3
                    // The S3 ones have dainely-media/images/
                    if (strpos($html, "dainely-media/images/" . $filename) !== false) {
                        $oldPath = "images/" . $filename;
                        $newPath = "dainely/media/blogs/" . $filename;
                        if (Storage::disk("s3")->exists($oldPath)) {
                            Storage::disk("s3")->copy($oldPath, $newPath);
                            $html = str_replace("images/" . $filename, "dainely/media/blogs/" . $filename, $html);
                            $changed = true;
                            $log[] = "Copied $oldPath to $newPath";
                        }
                    }
                }
            }
            
            if ($changed) {
                $blog->content = $html;
                $blog->save();
            }
        }
        
        return "Migration complete! Log: <br>" . implode("<br>", $log);
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});


Route::get("/test-unsplash", function () {
    try {
        $url = "https://images.unsplash.com/photo-1664286074176-5206ee5dc878?w=600&auto=format&fit=crop&q=60&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MXx8YmVsdHLlbnwwfHwwfHx8Mg3D%CD";
        $contents = file_get_contents($url);
        $filename = "dummy-test-" . time() . ".jpg";
        $path = "dainely/media/Landing page/" . $filename;
        \Illuminate\Support\Facades\Storage::disk("s3")->put($path, $contents, "public");
        $s3Url = \Illuminate\Support\Facades\Storage::disk("s3")->url($path);
        
        return "Success! Unsplash image uploaded to Landing page folder. URL: <a href=\"$s3Url\" target=\"_blank\">$s3Url</a>";
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});


Route::get("/organize-all-media", function () {
    $log = [];
    
    $processModel = function ($model, $type, &$log) {
        $changed = false;
        $patterns = [
            "blog" => "/(optimized\\/editor|dainely\\/media\\/blogs|dainely\\/blogs)\\/([^\s\\\"\\'\\<\\>]+)/i",
            "edu" => "/(optimized\\/editor|dainely\\/media\\/education|dainely\\/media\\/Education|dainely\\/education)\\/([^\s\\\"\\\'\\<\\>]+)/i",
            "landing" => "/(optimized\\/editor|Lanfing%20Pages|Landing%20Pages|Landing Pages\\/[^\s\\\"\\'\\<\\>\\/]+|dainely\\/media\\/landing|dainely\\/media\\/Landing page)\\/([^\s\\\"\\\'\\<\\>]+)/i",
            "bundle" => "/(optimized\\/editor|dainely\\/media\\/others)\\/([^\s\\\"\\\'\\<\\>]+)/i"
        ];
        $dest = [
            "blog" => "blogs/",
            "edu" => "education/",
            "landing" => "Landing Pages/",
            "bundle" => "others/"
        ];
        
        $attrs = $model->getAttributes();
        foreach ($attrs as $key => $val) {
            if (is_string($val)) {
                if (preg_match_all($patterns[$type], $val, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $match) {
                        $oldPath = $match[1] . "/" . $match[2];
                        $newPath = $dest[$type] . $match[2];
                        
                        $copyOld = urldecode($oldPath);
                        $copyNew = urldecode($newPath);

                        if ($copyOld !== $copyNew) {
                            try {
                                if (\Illuminate\Support\Facades\Storage::disk("s3")->exists($copyOld)) {
                                    \Illuminate\Support\Facades\Storage::disk("s3")->copy($copyOld, $copyNew);
                                } elseif (\Illuminate\Support\Facades\Storage::disk("s3")->exists($oldPath)) {
                                    \Illuminate\Support\Facades\Storage::disk("s3")->copy($oldPath, $newPath);
                                }
                                $val = str_replace($oldPath, $newPath, $val);
                                $val = str_replace(addcslashes($oldPath, "/"), addcslashes($newPath, "/"), $val);
                                $changed = true;
                                $log[] = "Moved $oldPath -> $newPath";
                            } catch (\Exception $e) {
                                $log[] = "Copy error for $oldPath: " . $e->getMessage();
                            }
                        }
                    }
                }
                $attrs[$key] = $val;
            }
        }
        
        if ($changed) {
            $model->setRawAttributes($attrs);
            $model->save();
        }
    };

    try {
        // 1. Blogs
        foreach (\App\Models\BlogPostTranslation::all() as $m) $processModel($m, "blog", $log);
        foreach (\App\Models\BlogPost::all() as $m) $processModel($m, "blog", $log);
        
        // 2. Education
        foreach (\App\Models\Catalog\EducationPage::all() as $m) $processModel($m, "edu", $log);
        
        // 3. Landing Pages
        foreach (\App\Models\Supabase\PageBlock::all() as $m) {
            $processModel($m, "landing", $log);
        }

        // 4. Bundles
        foreach (\App\Models\Supabase\ProductBundle::all() as $m) {
            $processModel($m, "bundle", $log);
        }
        
        \Illuminate\Support\Facades\Cache::flush();

        return "Done! Reorganized media to clean root folders. Log:<br>" . implode("<br>", $log);
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage() . " at line " . $e->getLine();
    }
});

Route::get("/debug-media", function () {
    $html = "<h2>Landing Page Blocks</h2><ul>";
    try {
        $blocks = \App\Models\Supabase\PageBlock::all();
        foreach ($blocks as $b) {
            $html .= "<li><b>Block {$b->id}</b>: " . htmlspecialchars($b->content) . "</li>";
        }
        $html .= "</ul>";
        return $html;
    } catch (\Exception $e) {
        return "Error: " . $e->getMessage();
    }
});

Route::get('/force-reset-admin', function() {
    $path = base_path('.env');
    if (file_exists($path)) {
        $env = file_get_contents($path);
        
        if (preg_match('/^ADMIN_EMAIL=.*$/m', $env)) {
            $env = preg_replace('/^ADMIN_EMAIL=.*$/m', 'ADMIN_EMAIL="admin@dainelylab.com"', $env);
        } else {
            $env .= "\nADMIN_EMAIL=\"admin@dainelylab.com\"";
        }

        if (preg_match('/^ADMIN_PASSWORD=.*$/m', $env)) {
            $env = preg_replace('/^ADMIN_PASSWORD=.*$/m', 'ADMIN_PASSWORD="R@thi123"', $env);
        } else {
            $env .= "\nADMIN_PASSWORD=\"R@thi123\"";
        }
        file_put_contents($path, $env);
    }
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return 'Password reset to R@thi123 and cache cleared! You can now login.';
});