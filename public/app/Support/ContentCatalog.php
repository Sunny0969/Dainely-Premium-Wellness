<?php

namespace App\Support;

/**
 * Static education + blog entities for related_content (stable integer IDs).
 * Education/blog live as route-backed pages, not Supabase rows.
 */
class ContentCatalog
{
    /**
     * @return list<array{id:int,slug:string,route:string,title:string,titles?:array<string,string>}>
     */
    public static function educationPages(): array
    {
        if (\Illuminate\Support\Facades\Schema::hasTable('education_pages')) {
            return \App\Models\Catalog\EducationPage::where('is_active', true)
                ->get()
                ->map(fn ($page) => [
                    'id' => $page->id,
                    'slug' => $page->slug,
                    'route' => 'education.show', // New dynamic route
                    'title' => $page->title,
                    'titles' => [
                        'en' => $page->title,
                    ],
                ])
                ->toArray();
        }

        return [];
    }

    /**
     * @return list<array{id:int,slug:string,title:string}>
     */
    public static function blogPosts(): array
    {
        return [
            [
                'id' => 1,
                'slug' => 'root-cause-chronic-back-pain',
                'title' => 'The Root Cause of Chronic Back Pain Most Doctors Miss',
            ],
            [
                'id' => 2,
                'slug' => 'science-of-sciatica',
                'title' => 'The Science of Sciatica: Why Your Leg Hurts When Your Back Is the Problem',
            ],
            [
                'id' => 3,
                'slug' => '5-posture-mistakes',
                'title' => '5 Posture Mistakes That Are Silently Destroying Your Spine',
            ],
            [
                'id' => 4,
                'slug' => 'how-decompression-belts-work',
                'title' => 'How Lumbar Decompression Belts Work: The Biomechanics Explained',
            ],
            [
                'id' => 5,
                'slug' => 'neck-pain-spinal-connection',
                'title' => 'Neck Pain & Upper Back Tension: The Hidden Spinal Connection',
            ],
            [
                'id' => 6,
                'slug' => '4-week-back-pain-recovery',
                'title' => 'The 4-Week Back Pain Recovery Protocol: A Step-by-Step Guide',
            ],
        ];
    }

    public static function educationById(int $id): ?array
    {
        return collect(self::educationPages())->firstWhere('id', $id);
    }

    public static function educationBySlug(string $slug): ?array
    {
        return collect(self::educationPages())->firstWhere('slug', $slug);
    }

    public static function blogById(int $id): ?array
    {
        return collect(self::blogPosts())->firstWhere('id', $id);
    }

    public static function blogBySlug(string $slug): ?array
    {
        return collect(self::blogPosts())->firstWhere('slug', $slug);
    }

    public static function educationTitle(array $page, string $locale): string
    {
        return $page['titles'][$locale] ?? $page['title'];
    }
}
