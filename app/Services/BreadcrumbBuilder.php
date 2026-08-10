<?php

namespace App\Services;

use App\Models\Supabase\LandingPage;
use App\Support\ContentCatalog;
use App\Support\SupabaseDb;

/**
 * Phase 2 §7.3 — breadcrumbs from URL hierarchy and optional parent_id.
 */
class BreadcrumbBuilder
{
    /**
     * @param  list<array{name:string,url:?string}>  $trail
     * @return list<array{name:string,url:?string}>
     */
    public function forProduct(string $locale, string $productTitle, string $productUrl): array
    {
        return [
            ['name' => __('products.breadcrumb_home'), 'url' => route('home', ['locale' => $locale])],
            ['name' => __('nav.products'), 'url' => route('products.index', ['locale' => $locale])],
            ['name' => $productTitle, 'url' => $productUrl],
        ];
    }

    /**
     * @return list<array{name:string,url:?string}>
     */
    public function forLandingPage(LandingPage $page, string $locale): array
    {
        $trail = [
            ['name' => __('products.breadcrumb_home'), 'url' => route('home', ['locale' => $locale])],
        ];

        $ancestors = $this->landingAncestors($page);
        foreach ($ancestors as $ancestor) {
            $trail[] = [
                'name' => $ancestor->title,
                'url' => route('landing.show', ['locale' => $locale, 'slug' => $ancestor->slug]),
            ];
        }

        $trail[] = [
            'name' => $page->title,
            'url' => route('landing.show', ['locale' => $locale, 'slug' => $page->slug]),
        ];

        return $trail;
    }

    /**
     * @return list<array{name:string,url:?string}>
     */
    public function forEducation(string $locale, string $slug): array
    {
        $page = ContentCatalog::educationBySlug($slug);
        $title = $page ? ContentCatalog::educationTitle($page, $locale) : ucwords(str_replace('-', ' ', $slug));

        return [
            ['name' => __('products.breadcrumb_home'), 'url' => route('home', ['locale' => $locale])],
            ['name' => __('nav.education') !== 'nav.education' ? __('nav.education') : 'Education', 'url' => null],
            ['name' => $title, 'url' => $page ? route($page['route'], ['locale' => $locale]) : null],
        ];
    }

    /**
     * @return list<array{name:string,url:?string}>
     */
    public function forBlog(string $locale, string $title, string $url): array
    {
        return [
            ['name' => __('products.breadcrumb_home'), 'url' => route('home', ['locale' => $locale])],
            ['name' => __('nav.blog') !== 'nav.blog' ? __('nav.blog') : 'Blog', 'url' => route('blog.index', ['locale' => $locale])],
            ['name' => $title, 'url' => $url],
        ];
    }

    /**
     * Schema.org BreadcrumbList node.
     *
     * @param  list<array{name:string,url:?string}>  $trail
     */
    public function toSchema(array $trail, string $pageUrl): array
    {
        $elements = [];
        $position = 1;

        foreach ($trail as $crumb) {
            $item = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $crumb['name'],
            ];
            if (! empty($crumb['url'])) {
                $item['item'] = $crumb['url'];
            }
            $elements[] = $item;
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $pageUrl . '#breadcrumb',
            'itemListElement' => $elements,
        ];
    }

    /**
     * @return list<LandingPage>
     */
    protected function landingAncestors(LandingPage $page): array
    {
        if (! SupabaseDb::available() || empty($page->parent_id)) {
            return [];
        }

        $ancestors = [];
        $guard = 0;
        $currentId = $page->parent_id;

        while ($currentId && $guard < 5) {
            $parent = LandingPage::query()
                ->where('id', $currentId)
                ->where('published', true)
                ->first();

            if (! $parent) {
                break;
            }

            array_unshift($ancestors, $parent);
            $currentId = $parent->parent_id;
            $guard++;
        }

        return $ancestors;
    }
}
