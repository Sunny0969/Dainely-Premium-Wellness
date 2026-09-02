<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for fetching and caching product reviews from the Judge.me API.
 *
 * Reviews are fetched server-side using the Private API token (never exposed
 * to the client). Results are cached using Laravel's file cache driver.
 */
class ReviewService
{
    protected string $apiToken;
    protected string $shopDomain;
    protected int    $cacheTtl;
    protected bool   $verifySsl;

    /**
     * Maps each site product handle to ALL Judge.me handles whose reviews
     * should be aggregated together.  Many Shopify stores have duplicate
     * products (A/B tests, upsell variants, legacy slugs) that all represent
     * the same physical item.
     */
    protected static array $handleGroups = [
        // ── Dainely Belt (all variants) ──────────────────────────
        'dainely-comfort-belt' => [
            'dainely-belt',
            'back-belt',
            'dainely-premium-belt-relieve-back-pain-sciatica',
            'db',
            'belt',
            'belt-2',
            'back-belt-1',
            'dainely-belt-for-lower-back-hip-pelvic-pain-relief-for-women-men-compression-lumbar-support-brace',
            'dainely™-belt-funnelish',
            'dainely™-belt-test',
            'dainely-comfort-belt',
        ],
        // Aliases that resolve to dainely-comfort-belt via ProductSlugResolver
        'dainely-belt'       => 'dainely-comfort-belt',
        'dainely-belt-2-b'   => 'dainely-comfort-belt',
        'dainely-belt-2-c'   => 'dainely-comfort-belt',

        // ── Knee Brace ───────────────────────────────────────────
        'brace' => [
            'brace',
            'brace-upsell',
            'dainely-knee-brace',
        ],
        'dainely-knee-brace' => 'brace',

        // ── Neck Stretcher ───────────────────────────────────────
        'stretcher' => [
            'stretcher',
            'stretcher-upsell',
            'dainely-neck-stretcher',
        ],
        'dainely-neck-stretcher' => 'stretcher',

        // ── Back Stretcher ───────────────────────────────────────
        'dainely™-orthopedic-back-stretcher' => [
            'dainely™-orthopedic-back-stretcher',
            'dainely-orthopedic-back-stretcher',
            'back-stretcher',
        ],
        'dainely-orthopedic-back-stretcher' => 'dainely™-orthopedic-back-stretcher',
        'back-stretcher'                    => 'dainely™-orthopedic-back-stretcher',

        // ── Neck Cloud ───────────────────────────────────────────
        'neck-pain' => [
            'neck-pain',
        ],

        // ── Back Patches ─────────────────────────────────────────
        'back-pain-relief-patches-20-pcs' => [
            'back-pain-relief-patches-20-pcs',
        ],

        // ── Heated Jacket ────────────────────────────────────────
        'dainely-unisex-heated-jacket' => [
            'jacket',
            'dainely-unisex-heated-jacket',
        ],

        // ── Foot Massager ────────────────────────────────────────
        'dainely-foot-massager' => [
            'dainely-foot-massager',
            'dainely™-foot-massager',
        ],
        'dainely™-foot-massager' => 'dainely-foot-massager',

        // ── Massager ─────────────────────────────────────────────
        'dainely-massager' => [
            'dainely-massager',
            'dainely™-massager',
        ],
        'dainely™-massager' => 'dainely-massager',

        // ── Shoulder Brace ───────────────────────────────────────
        'shoulder-brace' => [
            'shoulder-brace',
            'dainely-shoulder-brace',
        ],
        'dainely-shoulder-brace' => 'shoulder-brace',

        // ── Ball Massager ────────────────────────────────────────
        'dainely-ball-massager' => [
            'dainely-ball-massager',
            'dainely™-ball-massager',
            'dainely-ball-massager-1',
        ],
        'dainely™-ball-massager'  => 'dainely-ball-massager',
        'dainely-ball-massager-1' => 'dainely-ball-massager',

        // ── RelaxaLeg System ─────────────────────────────────────
        'leg-massager' => [
            'leg-massager',
            'relaxaleg-system',
            'dainely-relaxaleg-system',
            'relaxaleg',
        ],
        'relaxaleg-system'          => 'leg-massager',
        'dainely-relaxaleg-system'  => 'leg-massager',
        'relaxaleg'                 => 'leg-massager',

        // ── Tourmaline Belt ──────────────────────────────────────
        'dainely™-tourmaline-belt' => [
            'dainely™-tourmaline-belt',
            'dainely-tourmaline-belt',
            'tourmaline-belt',
        ],
        'dainely-tourmaline-belt' => 'dainely™-tourmaline-belt',
        'tourmaline-belt'         => 'dainely™-tourmaline-belt',

        // ── DMEDE Daily Comfort System (shares belt review pool) ─
        'dainely-daily-comfort-system'        => 'dainely-comfort-belt',
        'dmede-daily-support'                 => 'dainely-daily-comfort-system',
        'dmede-daily-support-recovery-system' => 'dainely-daily-comfort-system',

        // ── ErgoCushion ──────────────────────────────────────────
        'cushion' => [
            'cushion',
            'dainely-cushion',
            'ergocushion',
        ],
        'dainely-cushion' => 'cushion',
        'ergocushion'     => 'cushion',

        // ── Mushroom Coffee ──────────────────────────────────────
        'functional-mushroom-coffee' => [
            'functional-mushroom-coffee',
            'mushroom-coffee',
            'coffee',
        ],
        'mushroom-coffee' => 'functional-mushroom-coffee',
        'coffee'          => 'functional-mushroom-coffee',

        // ── Mouthpiece ───────────────────────────────────────────
        'mouthpiece' => [
            'mouthpiece',
        ],
    ];

    /**
     * Map each Judge.me product handle to its internal product ID.
     */
    protected static array $handleToJudgemeId = [
        '1x-dainely™-knee-brace-funnelish' => 1938085535,
        '2x-dainely™-knee-brace' => 1938085537,
        '3x-dainely™-knee-brace-funnelish' => 1938085538,
        'back-belt' => 1938085524,
        'back-belt-1' => 1938085529,
        'back-pain-relief-patches-20-pcs' => 1938085548,
        'belt' => 1938085526,
        'belt-2' => 1938085518,
        'brace' => 1938085519,
        'brace-upsell' => 1938085549,
        'cave' => 429083455,
        'cupper' => 352033720,
        'cushion' => 379053216,
        'dainely™-ball-massager' => 391965730,
        'dainely-belt' => 1938085530,
        'dainely-belt-2x' => 1938085547,
        'dainely-belt-for-lower-back-hip-pelvic-pain-relief-for-women-men-compression-lumbar-support-brace' => 1938085522,
        'dainely™-belt-funnelish' => 1938085533,
        'dainely™-belt-test' => 1938085546,
        'dainely™-foot-massager' => 1938085541,
        'dainely™-massager' => 1938085540,
        'dainely™-orthopedic-back-stretcher' => 376610616,
        'dainely-premium-belt-relieve-back-pain-sciatica' => 424230411,
        'dainely™-tourmaline-belt' => 389679824,
        'db' => 1938085551,
        'leg-massager' => 1938085511,
        'mouthpiece' => 1938085531,
        'neck-pain' => 368723407,
        'shoulder-brace' => 404102286,
        'stretcher' => 1938085528,
        'stretcher-upsell' => 1938085550,
    ];

    public function __construct()
    {
        $this->apiToken  = config('judgeme.api_token', '');
        $this->shopDomain = config('judgeme.shop_domain', 'ididit555.myshopify.com');
        $this->cacheTtl  = (int) config('judgeme.cache_ttl', 3600);
        $this->verifySsl = (bool) config('judgeme.verify_ssl', false);
    }

    /**
     * Resolve a site handle to the canonical handle group key.
     */
    protected function resolveCanonicalHandle(string $handle): string
    {
        // Follow alias chains: if the value is a string, it points to the canonical
        if (isset(self::$handleGroups[$handle]) && is_string(self::$handleGroups[$handle])) {
            return self::$handleGroups[$handle];
        }

        return $handle;
    }

    /**
     * Get all Judge.me handles whose reviews should be aggregated for a product.
     */
    protected function getHandleGroup(string $handle): array
    {
        $canonical = $this->resolveCanonicalHandle($handle);

        if (isset(self::$handleGroups[$canonical]) && is_array(self::$handleGroups[$canonical])) {
            return self::$handleGroups[$canonical];
        }

        // Unknown product — just search by the handle itself
        return [$handle];
    }

    /**
     * Fetch reviews for a product (with caching).
     *
     * @return array{reviews: array, total_count: int, average_rating: float, rating_breakdown: array}
     */
    public function getProductReviews(string $handle, int $limit = 30): array
    {
        if ($this->apiToken === '') {
            return $this->emptyResult();
        }

        $canonical = $this->resolveCanonicalHandle($handle);
        $cacheKey  = "judgeme_reviews_{$canonical}";

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($canonical, $limit) {
            return $this->fetchAndMergeReviews($canonical, $limit);
        });
    }

    /**
     * Get aggregate stats (derived from the same reviews cache).
     *
     * @return array{average_rating: float, total_reviews: int, rating_breakdown: array}
     */
    public function getProductStats(string $handle): array
    {
        if ($this->apiToken === '') {
            return ['average_rating' => 0, 'total_reviews' => 0, 'rating_breakdown' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]];
        }

        $data = $this->getProductReviews($handle, 100);

        return [
            'average_rating'   => $data['average_rating'],
            'total_reviews'    => $data['total_count'],
            'rating_breakdown' => $data['rating_breakdown'],
        ];
    }

    /**
     * Read cached review stats only — never triggers an API call.
     *
     * @return array{average_rating: float, total_reviews: int, rating_breakdown: array}
     */
    public function getCachedStats(string $handle): array
    {
        $default = [
            'average_rating'   => 0,
            'total_reviews'    => 0,
            'rating_breakdown' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
        ];

        if ($this->apiToken === '') {
            return $default;
        }

        $canonical = $this->resolveCanonicalHandle($handle);
        $cached    = Cache::get("judgeme_reviews_{$canonical}");

        if (! is_array($cached)) {
            return $default;
        }

        return [
            'average_rating'   => $cached['average_rating'] ?? 0,
            'total_reviews'    => $cached['total_count'] ?? 0,
            'rating_breakdown' => $cached['rating_breakdown'] ?? $default['rating_breakdown'],
        ];
    }

    /**
     * Read cached stats for many product handles (cache-only, deduped by canonical group).
     *
     * @param  list<string>  $handles
     * @return array<string, array{average_rating: float, total_reviews: int, rating_breakdown: array}>
     */
    public function getCachedStatsForHandles(array $handles): array
    {
        $default = [
            'average_rating'   => 0,
            'total_reviews'    => 0,
            'rating_breakdown' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
        ];

        $handles = array_values(array_unique(array_filter($handles, fn ($h) => $h !== '')));
        if ($handles === []) {
            return [];
        }

        if ($this->apiToken === '') {
            return array_fill_keys($handles, $default);
        }

        $canonicalByHandle = [];
        foreach ($handles as $handle) {
            $canonicalByHandle[$handle] = $this->resolveCanonicalHandle($handle);
        }

        $statsByCanonical = [];
        foreach (array_unique(array_values($canonicalByHandle)) as $canonical) {
            $statsByCanonical[$canonical] = $this->getCachedStats($canonical);
        }

        $result = [];
        foreach ($handles as $handle) {
            $result[$handle] = $statsByCanonical[$canonicalByHandle[$handle]] ?? $default;
        }

        return $result;
    }

    /**
     * Force-refresh reviews cache for one product group (used by cron / artisan).
     */
    public function warmCacheForHandle(string $handle): void
    {
        if ($this->apiToken === '') {
            return;
        }

        $canonical = $this->resolveCanonicalHandle($handle);
        Cache::forget("judgeme_reviews_{$canonical}");
        $this->getProductReviews($canonical, 100);
    }

    /**
     * Canonical handle keys that own a review group (for cache warmup).
     *
     * @return list<string>
     */
    public static function canonicalHandlesForWarmup(): array
    {
        $keys = [];
        foreach (self::$handleGroups as $key => $value) {
            if (is_array($value)) {
                $keys[] = $key;
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * Fetch, merge, and deduplicate reviews from all handles in a group (parallel HTTP).
     */
    protected function fetchAndMergeReviews(string $canonical, int $limit): array
    {
        $handles = $this->getHandleGroup($canonical);
        $allReviews = $this->fetchReviewsInParallel($handles);
        $apiTotals = config('judgeme.use_count_api', true)
            ? $this->fetchGroupReviewTotals($handles)
            : null;

        return $this->buildReviewResult($allReviews, $limit, $apiTotals);
    }

    /**
     * Sum Judge.me /reviews/count for every unique product_id in the handle group.
     * Falls back to shop-wide all_reviews_count when no product IDs are mapped.
     *
     * @param  list<string>  $handles
     * @return array{total_count:int, average_rating:?float}
     */
    protected function fetchGroupReviewTotals(array $handles): array
    {
        $productIds = [];
        foreach ($handles as $handle) {
            $id = self::$handleToJudgemeId[$handle] ?? null;
            if ($id !== null) {
                $productIds[$id] = true;
            }
        }
        $productIds = array_keys($productIds);

        $total = 0;
        if ($productIds !== []) {
            try {
                $responses = Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($productIds) {
                    $reqs = [];
                    foreach ($productIds as $id) {
                        $reqs[] = $pool->as('c_'.$id)
                            ->withOptions(['verify' => $this->verifySsl])
                            ->timeout(8)
                            ->connectTimeout(15)
                            ->get('https://judge.me/api/v1/reviews/count', [
                                'api_token' => $this->apiToken,
                                'shop_domain' => $this->shopDomain,
                                'product_id' => $id,
                            ]);
                    }

                    return $reqs;
                });
            } catch (\Throwable $e) {
                Log::warning('Judge.me count pool failed: '.$e->getMessage());
                $responses = [];
            }

            foreach ($productIds as $id) {
                $response = $responses['c_'.$id] ?? null;
                if ($response instanceof \Illuminate\Http\Client\Response && $response->successful()) {
                    $total += (int) ($response->json('count') ?? 0);
                }
            }
        }

        $shop = $this->fetchShopWideTotals();

        if ($total < 1 && config('judgeme.use_shop_totals_fallback', true)) {
            $total = (int) ($shop['total_count'] ?? 0);
        }

        return [
            'total_count' => $total,
            'average_rating' => $shop['average_rating'] ?? null,
        ];
    }

    /**
     * Shop-wide Judge.me totals (cached separately — used for avg + empty-group fallback).
     *
     * @return array{total_count:int, average_rating:float}
     */
    public function fetchShopWideTotals(): array
    {
        return Cache::remember('judgeme_shop_totals', $this->cacheTtl, function () {
            $default = ['total_count' => 0, 'average_rating' => 4.8];

            if ($this->apiToken === '') {
                return $default;
            }

            try {
                $countRes = Http::withOptions(['verify' => $this->verifySsl])
                    ->timeout(8)
                    ->get('https://judge.me/api/v1/widgets/all_reviews_count', [
                        'api_token' => $this->apiToken,
                        'shop_domain' => $this->shopDomain,
                    ]);
                $ratingRes = Http::withOptions(['verify' => $this->verifySsl])
                    ->timeout(8)
                    ->get('https://judge.me/api/v1/widgets/all_reviews_rating', [
                        'api_token' => $this->apiToken,
                        'shop_domain' => $this->shopDomain,
                    ]);

                $count = (int) ($countRes->json('all_reviews_count') ?? 0);
                if ($count < 1 && $countRes->successful() === false) {
                    $alt = Http::withOptions(['verify' => $this->verifySsl])
                        ->timeout(8)
                        ->get('https://judge.me/api/v1/reviews/count', [
                            'api_token' => $this->apiToken,
                            'shop_domain' => $this->shopDomain,
                        ]);
                    $count = (int) ($alt->json('count') ?? 0);
                }

                $rating = (float) ($ratingRes->json('all_reviews_rating') ?? 4.8);

                return [
                    'total_count' => $count,
                    'average_rating' => $rating > 0 ? round($rating, 1) : 4.8,
                ];
            } catch (\Throwable $e) {
                Log::warning('Judge.me shop totals failed: '.$e->getMessage());

                return $default;
            }
        });
    }

    /**
     * Fetch reviews for multiple handles concurrently via Http::pool().
     *
     * @param  list<string>  $handles
     * @return list<array<string, mixed>>
     */
    protected function fetchReviewsInParallel(array $handles): array
    {
        if ($handles === []) {
            return [];
        }

        // Deduplicate by Judge.me product_id — one request per unique ID
        $targets = [];
        foreach ($handles as $judgemeHandle) {
            $productId = self::$handleToJudgemeId[$judgemeHandle] ?? null;
            $poolKey   = $productId !== null ? 'id_'.$productId : 'handle_'.$judgemeHandle;

            if (! isset($targets[$poolKey])) {
                $targets[$poolKey] = [
                    'product_id'   => $productId,
                    'match_handle' => $productId === null ? $judgemeHandle : null,
                ];
            }
        }

        try {
            $responses = \Illuminate\Support\Facades\Http::pool(function (\Illuminate\Http\Client\Pool $pool) use ($targets) {
                $requests = [];
                foreach ($targets as $key => $target) {
                    $params = [
                        'api_token'   => $this->apiToken,
                        'shop_domain' => $this->shopDomain,
                        'per_page'    => 100,
                        'page'        => 1,
                    ];

                    if ($target['product_id'] !== null) {
                        $params['product_id'] = $target['product_id'];
                    }

                    $requests[] = $pool->as($key)
                        ->withOptions(['verify' => $this->verifySsl])
                        ->timeout(8)
                        ->connectTimeout(15)
                        ->get('https://judge.me/api/v1/reviews', $params);
                }

                return $requests;
            });
        } catch (\Throwable $e) {
            Log::error('Judge.me parallel fetch failed: '.$e->getMessage());

            return [];
        }

        $allReviews = [];

        foreach ($targets as $key => $target) {
            $response = $responses[$key] ?? null;

            if ($response === null || !($response instanceof \Illuminate\Http\Client\Response) || ! $response->successful()) {
                if ($response instanceof \Throwable) {
                    Log::warning("Judge.me parallel pool exception for key {$key}: " . $response->getMessage());
                } elseif ($response !== null && ($response instanceof \Illuminate\Http\Client\Response)) {
                    Log::warning("Judge.me API HTTP {$response->status()} for pool key {$key}");
                }
                continue;
            }

            foreach ($response->json()['reviews'] ?? [] as $review) {
                if (! ($review['published'] ?? false)) {
                    continue;
                }

                if ($target['product_id'] !== null) {
                    $allReviews[] = $review;
                    continue;
                }

                if (($review['product_handle'] ?? '') === $target['match_handle']) {
                    $allReviews[] = $review;
                }
            }
        }

        return $allReviews;
    }

    /**
     * Deduplicate, sort, and map raw reviews into the cached result shape.
     *
     * @param  list<array<string, mixed>>  $allReviews
     * @param  array{total_count:int, average_rating:?float}|null  $apiTotals
     * @return array{reviews: array, total_count: int, average_rating: float, rating_breakdown: array}
     */
    protected function buildReviewResult(array $allReviews, int $limit, ?array $apiTotals = null): array
    {
        $uniqueById = [];
        foreach ($allReviews as $review) {
            $id = $review['id'] ?? null;
            if ($id !== null) {
                if (isset($uniqueById[$id])) {
                    $existing = $uniqueById[$id];
                    $hasNewMedia = !empty($review['pictures']) || !empty($review['videos']);
                    $hasOldMedia = !empty($existing['pictures']) || !empty($existing['videos']);
                    if (($hasNewMedia && !$hasOldMedia) || (strtotime($review['created_at'] ?? '') > strtotime($existing['created_at'] ?? ''))) {
                        $uniqueById[$id] = $review;
                    }
                } else {
                    $uniqueById[$id] = $review;
                }
            } else {
                $uniqueById[] = $review;
            }
        }
        $allReviews = array_values($uniqueById);

        // Deduplicate by body text (case-insensitive, trimmed) to filter import duplication (e.g. Luis G. vs Luis Gordon)
        $uniqueByBody = [];
        foreach ($allReviews as $review) {
            $bodyKey = strtolower(trim($review['body'] ?? ''));
            if (empty($bodyKey)) {
                $bodyKey = 'empty_' . ($review['id'] ?? uniqid());
            }
            
            if (isset($uniqueByBody[$bodyKey])) {
                $existing = $uniqueByBody[$bodyKey];
                $hasNewMedia = !empty($review['pictures']) || !empty($review['videos']);
                $hasOldMedia = !empty($existing['pictures']) || !empty($existing['videos']);
                
                // Prefer keeping the review with media, or the newer one
                if (($hasNewMedia && !$hasOldMedia) || (strtotime($review['created_at'] ?? '') > strtotime($existing['created_at'] ?? ''))) {
                    $uniqueByBody[$bodyKey] = $review;
                }
            } else {
                $uniqueByBody[$bodyKey] = $review;
            }
        }

        $reviews = array_values($uniqueByBody);

        // Sort by: pinned first, then featured, then reviews with pictures/videos first, then newest
        usort($reviews, function ($a, $b) {
            if (($a['pinned'] ?? false) !== ($b['pinned'] ?? false)) {
                return ($b['pinned'] ?? false) <=> ($a['pinned'] ?? false);
            }
            if (($a['featured'] ?? false) !== ($b['featured'] ?? false)) {
                return ($b['featured'] ?? false) <=> ($a['featured'] ?? false);
            }
            
            // Check for media
            $aHasMedia = !empty($a['pictures']) || !empty($a['videos']) || ($a['has_published_pictures'] ?? false) || ($a['has_published_videos'] ?? false);
            $bHasMedia = !empty($b['pictures']) || !empty($b['videos']) || ($b['has_published_pictures'] ?? false) || ($b['has_published_videos'] ?? false);
            if ($aHasMedia !== $bHasMedia) {
                return $bHasMedia <=> $aHasMedia;
            }
            
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        // Breakdown from the downloaded sample (page-1 samples; may be << real total)
        $sampleCount = count($reviews);
        $ratingBreakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $sumRating = 0;

        foreach ($reviews as $review) {
            $rating = (int) ($review['rating'] ?? 5);
            $ratingBreakdown[$rating] = ($ratingBreakdown[$rating] ?? 0) + 1;
            $sumRating += $rating;
        }

        $sampleAverage = $sampleCount > 0 ? round($sumRating / $sampleCount, 1) : 0;

        // Prefer authoritative Judge.me count API (10K+) over sample size (~dozens–hundreds).
        $apiCount = (int) ($apiTotals['total_count'] ?? 0);
        $totalCount = $apiCount > $sampleCount ? $apiCount : $sampleCount;

        $apiAverage = isset($apiTotals['average_rating']) ? (float) $apiTotals['average_rating'] : 0.0;
        $averageRating = $sampleCount > 0
            ? $sampleAverage
            : ($apiAverage > 0 ? round($apiAverage, 1) : 0);

        // Limit to requested number for display
        $displayReviews = array_slice($reviews, 0, $limit);

        // Map reviews into a clean display format
        $mapped = array_map([$this, 'mapReviewForDisplay'], $displayReviews);

        return [
            'reviews'          => $mapped,
            'total_count'      => $totalCount,
            'average_rating'   => $averageRating,
            'rating_breakdown' => $ratingBreakdown,
        ];
    }

    /**
     * Map a raw Judge.me review into a clean display-friendly array.
     */
    protected function mapReviewForDisplay(array $review): array
    {
        $pictures = [];
        foreach ($review['pictures'] ?? [] as $pic) {
            if (! ($pic['hidden'] ?? false) && ! empty($pic['urls'])) {
                $pictures[] = [
                    'thumb'    => $pic['urls']['small'] ?? $pic['urls']['compact'] ?? '',
                    'original' => $pic['urls']['original'] ?? $pic['urls']['huge'] ?? '',
                ];
            }
        }

        $videos = [];
        foreach ($review['videos'] ?? [] as $vid) {
            if (! ($vid['hidden'] ?? false)) {
                $mp4  = $vid['mp4_url'] ?? '';
                $hls  = $vid['hls_url'] ?? '';
                $raw  = $vid['video_url'] ?? '';
                $play = $mp4 ?: ($raw !== '' && ! str_contains($raw, '.m3u8') ? $raw : '');

                if ($play === '' && $hls !== '') {
                    $play = $hls;
                }

                if ($play !== '') {
                    $videos[] = [
                        'url' => $play,
                        'mp4' => $mp4 ?: ($play !== $hls ? $play : ''),
                        'hls' => $hls,
                    ];
                }
            }
        }

        // Generate fallback dynamic premium title based on rating if title is empty
        $title = $review['title'] ?? '';
        if (empty($title)) {
            $rating = (int) ($review['rating'] ?? 5);
            $fallbacks = [
                5 => ['Excellent!', 'Highly recommended!', 'Great product!', 'Perfect!', 'Love it!', 'Very satisfied!'],
                4 => ['Very good!', 'Good product', 'Satisfied', 'Recommended'],
                3 => ['Okay', 'Average quality', 'Satisfied'],
                2 => ['Disappointed', 'Could be better'],
                1 => ['Not good', 'Disappointed']
            ];
            $list = $fallbacks[$rating] ?? $fallbacks[5];
            $index = ($review['id'] ?? 0) % count($list);
            $title = $list[$index];
        }

        $createdAt = $review['created_at'] ?? now()->toIso8601String();
        $carbon    = \Carbon\Carbon::parse($createdAt);

        return [
            'id'             => $review['id'],
            'title'          => $title,
            'body'           => $review['body'] ?? '',
            'rating'         => (int) ($review['rating'] ?? 5),
            'reviewer_name'  => $review['reviewer']['name'] ?? 'Anonymous',
            'verified'       => ($review['verified'] ?? '') === 'verified-purchase',
            'featured'       => $review['featured'] ?? false,
            'pinned'         => $review['pinned'] ?? false,
            'pictures'       => $pictures,
            'videos'         => $videos,
            'created_at'     => $carbon->toDateString(),
            'time_ago'       => $carbon->diffForHumans(),
            'product_title'  => $review['product_title'] ?? '',
            'product_handle' => $review['product_handle'] ?? '',
        ];
    }

    /**
     * Fetch and cache all reviews with media (pictures or videos) from the store.
     *
     * @return array
     */
    protected function getUniversalMediaReviews(): array
    {
        if ($this->apiToken === '') {
            return [];
        }

        return Cache::remember('judgeme_universal_media_reviews', $this->cacheTtl, function () {
            $allMediaReviews = [];

            // Fetch up to 10 pages of reviews to capture all historical media reviews
            for ($page = 1; $page <= 10; $page++) {
                try {
                    $response = Http::withOptions(['verify' => $this->verifySsl])
                        ->timeout(10)
                        ->get('https://judge.me/api/v1/reviews', [
                            'api_token'   => $this->apiToken,
                            'shop_domain' => $this->shopDomain,
                            'per_page'    => 100,
                            'page'        => $page,
                        ]);

                    if (!$response->successful()) {
                        Log::warning("Judge.me API returned HTTP {$response->status()} while fetching universal media reviews (page $page)");
                        break;
                    }

                    $data = $response->json();
                    $reviews = $data['reviews'] ?? [];

                    if (empty($reviews)) {
                        break;
                    }

                    foreach ($reviews as $review) {
                        $hasMedia = !empty($review['pictures']) || !empty($review['videos']) || ($review['has_published_pictures'] ?? false) || ($review['has_published_videos'] ?? false);
                        if ($hasMedia && ($review['published'] ?? false)) {
                            $allMediaReviews[] = $review;
                        }
                    }

                    if (count($reviews) < 100) {
                        break;
                    }
                } catch (\Throwable $e) {
                    Log::error("Error fetching universal media reviews on page {$page}: " . $e->getMessage());
                    break;
                }
            }

            return $allMediaReviews;
        });
    }

    /**
     * Return an empty result structure.
     */
    protected function emptyResult(): array
    {
        return [
            'reviews'          => [],
            'total_count'      => 0,
            'average_rating'   => 0,
            'rating_breakdown' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
        ];
    }
}
