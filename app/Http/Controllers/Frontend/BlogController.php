<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

class BlogController extends Controller
{
    public function index(string $locale)
    {
        $articles = $this->getArticles();
        return view('blog.index', compact('articles', 'locale'));
    }

    public function show(string $locale, string $slug)
    {
        $articles = $this->getArticles();
        $article  = collect($articles)->firstWhere('slug', $slug);

        if (!$article) {
            abort(404);
        }

        $related = collect($articles)
            ->filter(fn($a) => $a['slug'] !== $slug)
            ->take(3)
            ->values()
            ->toArray();

        return view('blog.show', compact('article', 'related', 'locale'));
    }

    protected function getArticles(): array
    {
        return [
            [
                'slug'     => 'root-cause-chronic-back-pain',
                'title'    => 'The Root Cause of Chronic Back Pain Most Doctors Miss',
                'excerpt'  => 'Over 80% of adults experience significant back pain. Yet most treatments address only symptoms. Our physiotherapy team explains the real root causes.',
                'image'    => 'blog-hero-back-pain.jpg',
                'category' => 'Back Pain',
                'author'   => 'Dr. M. Reinholt',
                'readtime' => '8 min read',
                'date'     => 'May 15, 2025',
            ],
            [
                'slug'     => 'science-of-sciatica',
                'title'    => 'The Science of Sciatica: Why Your Leg Hurts When Your Back Is the Problem',
                'excerpt'  => 'Sciatica is notoriously misunderstood. Many patients treat their leg pain without addressing the spinal compression triggering it.',
                'image'    => 'sciatica-edu.png',
                'category' => 'Sciatica',
                'author'   => 'Dr. S. Laroche',
                'readtime' => '6 min read',
                'date'     => 'May 8, 2025',
            ],
            [
                'slug'     => '5-posture-mistakes',
                'title'    => '5 Posture Mistakes That Are Silently Destroying Your Spine',
                'excerpt'  => 'Poor posture is not just about how you look — it causes structural changes to your spine over time.',
                'image'    => 'posture-edu.png',
                'category' => 'Posture',
                'author'   => 'Dr. A. Müller',
                'readtime' => '5 min read',
                'date'     => 'April 29, 2025',
            ],
            [
                'slug'     => 'how-decompression-belts-work',
                'title'    => 'How Lumbar Decompression Belts Work: The Biomechanics Explained',
                'excerpt'  => 'Not all back braces work the same way. This deep dive explains exactly how decompression belts differ from compression braces.',
                'image'    => 'dainely-belt-product.png',
                'category' => 'Product Guide',
                'author'   => 'Dr. M. Reinholt',
                'readtime' => '7 min read',
                'date'     => 'April 20, 2025',
            ],
            [
                'slug'     => 'neck-pain-spinal-connection',
                'title'    => 'Neck Pain & Upper Back Tension: The Hidden Spinal Connection',
                'excerpt'  => 'Neck pain and lower back pain are often treated separately — but our spine is one connected structure.',
                'image'    => 'neck-pain-edu.png',
                'category' => 'Neck Pain',
                'author'   => 'Dr. S. Laroche',
                'readtime' => '4 min read',
                'date'     => 'April 12, 2025',
            ],
            [
                'slug'     => '4-week-back-pain-recovery',
                'title'    => 'The 4-Week Back Pain Recovery Protocol: A Step-by-Step Guide',
                'excerpt'  => 'A systematic four-week protocol combining decompression therapy, targeted stretching, and postural retraining.',
                'image'    => 'mobility-edu.png',
                'category' => 'Recovery',
                'author'   => 'Dr. M. Reinholt',
                'readtime' => '10 min read',
                'date'     => 'April 5, 2025',
            ],
        ];
    }
}
