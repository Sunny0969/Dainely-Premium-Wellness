<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\RelatedContentResolver;
use App\Services\ShopifyService;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    protected ShopifyService $shopify;
    protected RelatedContentResolver $related;

    public function __construct(ShopifyService $shopify, RelatedContentResolver $related)
    {
        $this->shopify = $shopify;
        $this->related = $related;
    }

    public function index(string $locale)
    {
        $articles = Cache::remember("blog_index_articles_{$locale}", 3600, function () use ($locale) {
            $posts = BlogPost::with(['translations', 'category'])
                ->where('is_published', true)
                ->orderBy('published_at', 'desc')
                ->get();

            return $posts->map(function ($post) use ($locale) {
                $trans = $post->translation($locale);
                $catName = '';
                if ($post->category) {
                    $names = is_array($post->category->name) ? $post->category->name : json_decode($post->category->name, true);
                    $catName = $names[$locale] ?? $names['en'] ?? '';
                }

                return [
                    'slug' => $trans ? $trans->slug : '',
                    'title' => $trans ? $trans->title : '',
                    'excerpt' => $trans ? $trans->excerpt : '',
                    'image' => $post->resolveFeaturedImage(),
                    'category' => $catName,
                    'author' => $post->author_name ?: 'Dainely Editorial',
                    'readtime' => '6 min read',
                    'date' => $post->published_at ? $post->published_at->format('M d, Y') : '2026',
                ];
            })->filter(fn($a) => !empty($a['slug']) && !empty($a['title']))->values()->all();
        });

        $categories = Cache::remember("blog_categories_{$locale}", 3600, function () use ($locale) {
            return \App\Models\BlogCategory::all()->map(function($cat) use ($locale) {
                $names = is_array($cat->name) ? $cat->name : json_decode($cat->name, true);
                return [
                    'id' => $cat->id,
                    'name' => $names[$locale] ?? $names['en'] ?? '',
                ];
            })->filter(fn($c) => !empty($c['name']))->values()->toArray();
        });

        return view('blog.index', compact('articles', 'categories', 'locale'));
    }

    public function show(string $locale, string $slug)
    {
        // Cache the blog post to avoid heavy DB queries on every load
        $cacheKey = "blog_post_{$locale}_{$slug}";
        
        $articleData = Cache::remember($cacheKey, 3600, function () use ($locale, $slug) {
            $matchedPost = BlogPost::with(['translations', 'category'])
                ->where('is_published', true)
                ->whereHas('translations', function($q) use ($locale, $slug) {
                    $q->where('locale', $locale)->where('slug', $slug);
                })
                ->first();

            if (!$matchedPost) {
                return null;
            }

            $matchedTranslation = $matchedPost->translation($locale);
            
            $catName = '';
            if ($matchedPost->category) {
                $names = is_array($matchedPost->category->name) ? $matchedPost->category->name : json_decode($matchedPost->category->name, true);
                $catName = $names[$locale] ?? $names['en'] ?? '';
            }

            $tags = $matchedTranslation->tags;
            if (is_string($tags)) {
                $decoded = json_decode($tags, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $tags = $decoded;
                } else {
                    $tags = array_filter(array_map('trim', explode(',', $tags)));
                }
            }
            $tags = is_array($tags) ? $tags : [];

            $faqs = $matchedTranslation->faqs;
            if (is_string($faqs)) {
                $decoded = json_decode($faqs, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $faqs = $decoded;
                } else {
                    $faqs = [];
                }
            }
            $faqs = is_array($faqs) ? $faqs : [];

            return [
                'post_id' => $matchedPost->id,
                'article' => [
                    'id' => $matchedPost->id,
                    'slug' => $matchedTranslation->slug,
                    'title' => $matchedTranslation->title,
                    'excerpt' => $matchedTranslation->excerpt,
                    'content' => $matchedTranslation->content,
                    'image' => $matchedPost->resolveFeaturedImage(),
                    'image_alt' => $matchedTranslation->featured_image_alt ?: $matchedTranslation->title,
                    'category' => $catName,
                    'author' => $matchedPost->author_name ?: 'Dainely Editorial',
                    'readtime' => '6 min read',
                    'date' => $matchedPost->published_at ? $matchedPost->published_at->format('M d, Y') : '2026',
                    'tags' => $tags,
                    'faqs' => $faqs,
                    'meta_title' => $matchedTranslation->meta_title ?: $matchedTranslation->title,
                    'meta_description' => $matchedTranslation->meta_description ?: $matchedTranslation->excerpt,
                ]
            ];
        });

        if (!$articleData) {
            abort(404);
        }

        $article = $articleData['article'];
        $postId = $articleData['post_id'];

        // Cache related posts
        $related = Cache::remember("blog_related_{$locale}_{$postId}", 3600, function () use ($postId, $locale) {
            $relatedPosts = BlogPost::with(['translations', 'category'])
                ->where('is_published', true)
                ->where('id', '!=', $postId)
                ->latest('published_at')
                ->take(3)
                ->get();
                
            return $relatedPosts->map(function ($p) use ($locale) {
                $t = $p->translation($locale);
                $cName = '';
                if ($p->category) {
                    $names = is_array($p->category->name) ? $p->category->name : json_decode($p->category->name, true);
                    $cName = $names[$locale] ?? $names['en'] ?? '';
                }
                return [
                    'slug' => $t ? $t->slug : '',
                    'title' => $t ? $t->title : '',
                    'image' => $p->resolveFeaturedImage(),
                    'category' => $cName,
                    'readtime' => '6 min read',
                ];
            })->filter(fn($a) => !empty($a['slug']))->values()->toArray();
        });

        $graphLinks = $this->related->for('blog', $postId, $locale);

        $relatedLinks = $graphLinks->isNotEmpty()
            ? $graphLinks
            : collect($related)->map(fn ($a) => [
                'title' => $a['title'],
                'url' => route('blog.show', ['locale' => $locale, 'slug' => $a['slug']]),
                'type_label' => __('content_types.blog'),
                'type' => 'blog',
            ]);

        $featuredShopifyProduct = Cache::remember('featured_shopify_product_v1', 15 * 60, function () {
            return $this->shopify->featuredProduct();
        });

        $articleUrl = route('blog.show', ['locale' => $locale, 'slug' => $slug]);
        $breadcrumbs = [];

        return view('blog.show', compact('article', 'related', 'relatedLinks', 'breadcrumbs', 'locale', 'featuredShopifyProduct'));
    }
}
