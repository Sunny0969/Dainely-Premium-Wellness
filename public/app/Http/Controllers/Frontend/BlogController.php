<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\BreadcrumbBuilder;
use App\Services\RelatedContentResolver;
use App\Support\ContentCatalog;

class BlogController extends Controller
{
    public function __construct(
        protected RelatedContentResolver $related,
        protected BreadcrumbBuilder $breadcrumbs,
    ) {}

    public function index(string $locale)
    {
        $articles = $this->getArticles();

        return view('blog.index', compact('articles', 'locale'));
    }

    public function show(string $locale, string $slug)
    {
        $articles = $this->getArticles();
        $article = collect($articles)->firstWhere('slug', $slug);

        if (! $article) {
            abort(404);
        }

        $catalog = ContentCatalog::blogBySlug($slug);
        $blogId = (int) ($catalog['id'] ?? 0);

        $graphLinks = $blogId
            ? $this->related->for('blog', $blogId, $locale)
            : collect();

        // Legacy sidebar cards (full article arrays)
        $related = collect($articles)
            ->filter(fn ($a) => $a['slug'] !== $slug)
            ->take(3)
            ->values()
            ->toArray();

        $relatedLinks = $graphLinks->isNotEmpty()
            ? $graphLinks
            : collect($related)->map(fn ($a) => [
                'title' => $a['title'],
                'url' => route('blog.show', ['locale' => $locale, 'slug' => $a['slug']]),
                'type_label' => __('content_types.blog'),
                'type' => 'blog',
            ]);

        $articleUrl = route('blog.show', ['locale' => $locale, 'slug' => $slug]);
        $breadcrumbs = $this->breadcrumbs->forBlog($locale, $article['title'], $articleUrl);

        return view('blog.show', compact('article', 'related', 'relatedLinks', 'breadcrumbs', 'locale'));
    }

    protected function getArticles(): array
    {
        return array_map(function (array $post) {
            return [
                'slug' => $post['slug'],
                'title' => $post['title'],
                'excerpt' => $this->excerptFor($post['slug']),
                'image' => $this->imageFor($post['slug']),
                'category' => $this->categoryFor($post['slug']),
                'author' => 'Dainely Editorial',
                'readtime' => '6 min read',
                'date' => '2025',
            ];
        }, ContentCatalog::blogPosts());
    }

    protected function excerptFor(string $slug): string
    {
        return match ($slug) {
            'root-cause-chronic-back-pain' => 'Over 80% of adults experience significant back pain. Yet most treatments address only symptoms.',
            'science-of-sciatica' => 'Sciatica is notoriously misunderstood. Many patients treat leg pain without addressing spinal compression.',
            '5-posture-mistakes' => 'Poor posture is not just about how you look — it causes structural changes to your spine over time.',
            'how-decompression-belts-work' => 'Not all back braces work the same way. This deep dive explains decompression belts.',
            'neck-pain-spinal-connection' => 'Neck pain and lower back pain are often treated separately — but our spine is one connected structure.',
            '4-week-back-pain-recovery' => 'A systematic four-week protocol combining decompression therapy and postural retraining.',
            default => '',
        };
    }

    protected function imageFor(string $slug): string
    {
        return match ($slug) {
            'root-cause-chronic-back-pain' => 'blog-hero-back-pain.jpg',
            'science-of-sciatica' => 'sciatica-edu.png',
            '5-posture-mistakes' => 'posture-edu.png',
            'how-decompression-belts-work' => 'dainely-belt-product.png',
            'neck-pain-spinal-connection' => 'neck-pain-edu.png',
            '4-week-back-pain-recovery' => 'mobility-edu.png',
            default => 'blog-hero-back-pain.jpg',
        };
    }

    protected function categoryFor(string $slug): string
    {
        return match ($slug) {
            'root-cause-chronic-back-pain' => 'Back Pain',
            'science-of-sciatica' => 'Sciatica',
            '5-posture-mistakes' => 'Posture',
            'how-decompression-belts-work' => 'Product Guide',
            'neck-pain-spinal-connection' => 'Neck Pain',
            '4-week-back-pain-recovery' => 'Recovery',
            default => 'Education',
        };
    }
}
