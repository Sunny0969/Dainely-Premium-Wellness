<?php

namespace App\Http\Controllers\Admin;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogPostTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminBlogController extends AdminController
{
    public function index()
    {
        if (!$this->flashIfSupabaseOffline('Blogs Manager')) {
            return redirect('/dainely-admin-panel/dashboard');
        }

        $posts = BlogPost::with('translations', 'category')
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.blogs.index', compact('posts'));
    }

    public function create()
    {
        if (!$this->flashIfSupabaseOffline('Blogs Manager')) {
            return redirect('/dainely-admin-panel/dashboard');
        }

        $categories = BlogCategory::all();

        return view('admin.blogs.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!$this->flashIfSupabaseOffline('Blogs Manager')) {
            return redirect('/dainely-admin-panel/dashboard');
        }

        $request->validate([
            'blog_category_id' => 'required|exists:blog_categories,id',
            'featured_image' => 'nullable|image|max:4096',
            'author_name' => 'nullable|string|max:255',
            'author_avatar' => 'nullable|string|max:255',
            'author_title' => 'nullable|string|max:255',
            'is_published' => 'nullable|boolean',
            'translations' => 'required|array',
            'translations.en.title' => 'required|string|max:255',
        ]);

        $featuredImagePath = null;
        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $filename = time() . '-' . Str::slug($request->input('translations.en.title')) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $featuredImagePath = $filename;
        }

        $post = BlogPost::create([
            'blog_category_id' => $request->input('blog_category_id'),
            'featured_image' => $featuredImagePath,
            'author_name' => $request->input('author_name') ?: 'Dainely Editorial',
            'author_avatar' => $request->input('author_avatar'),
            'author_title' => $request->input('author_title'),
            'is_published' => $request->boolean('is_published'),
            'published_at' => $request->boolean('is_published') ? now() : null,
            'related_product_ids' => $request->input('related_product_ids') ?: [],
        ]);

        $submittedTranslations = $request->input('translations', []);
        $enFeaturedImageAlt = $request->input('translations.en.featured_image_alt');

        // Automatic translation for FR & DE
        if (empty($submittedTranslations['fr']['title']) || empty($submittedTranslations['de']['title'])) {
            try {
                $enData = $submittedTranslations['en'] ?? [];
                $fieldsToTranslate = [
                    'title' => $enData['title'] ?? null,
                    'excerpt' => $enData['excerpt'] ?? null,
                    'content' => $enData['content'] ?? null,
                    'tags' => $enData['tags'] ?? null,
                    'meta_title' => $enData['meta_title'] ?? null,
                    'meta_description' => $enData['meta_description'] ?? null,
                ];

                $enFaqs = $enData['faqs'] ?? [];
                foreach ($enFaqs as $idx => $faq) {
                    if (!empty($faq['question']) && !empty($faq['answer'])) {
                        $fieldsToTranslate["faq_{$idx}_question"] = $faq['question'];
                        $fieldsToTranslate["faq_{$idx}_answer"] = $faq['answer'];
                    }
                }

                $translatedPayloads = app(\App\Services\ContentTranslationService::class)->translateFields($fieldsToTranslate, ['fr', 'de']);

                foreach (['fr', 'de'] as $targetLoc) {
                    if (empty($submittedTranslations[$targetLoc]['title'])) {
                        $p = $translatedPayloads[$targetLoc] ?? [];
                        
                        $targetFaqs = [];
                        foreach ($enFaqs as $idx => $faq) {
                            $targetFaqs[] = [
                                'question' => $p["faq_{$idx}_question"] ?? $faq['question'],
                                'answer' => $p["faq_{$idx}_answer"] ?? $faq['answer']
                            ];
                        }

                        $submittedTranslations[$targetLoc] = [
                            'title' => $p['title'] ?? $enData['title'],
                            'excerpt' => $p['excerpt'] ?? $enData['excerpt'] ?? null,
                            'content' => $p['content'] ?? $enData['content'] ?? null,
                            'tags' => $p['tags'] ?? $enData['tags'] ?? '',
                            'faqs' => $targetFaqs,
                            'meta_title' => $p['meta_title'] ?? $enData['meta_title'] ?? null,
                            'meta_description' => $p['meta_description'] ?? $enData['meta_description'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                // Fallback to copy English content directly if translation fails
                foreach (['fr', 'de'] as $targetLoc) {
                    if (empty($submittedTranslations[$targetLoc]['title'])) {
                        $submittedTranslations[$targetLoc] = $submittedTranslations['en'];
                    }
                }
            }
        }

        foreach ($submittedTranslations as $locale => $transData) {
            if (empty($transData['title'])) {
                continue;
            }

            $faqsRaw = isset($transData['faqs']) ? $transData['faqs'] : [];
            $faqs = [];
            foreach ($faqsRaw as $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $faqs[] = [
                        'question' => $faq['question'],
                        'answer' => $faq['answer']
                    ];
                }
            }

            $tagsRaw = isset($transData['tags']) ? $transData['tags'] : '';
            $tags = is_string($tagsRaw) ? array_filter(array_map('trim', explode(',', $tagsRaw))) : (is_array($tagsRaw) ? $tagsRaw : []);

            BlogPostTranslation::create([
                'blog_post_id' => $post->id,
                'locale' => $locale,
                'title' => $transData['title'],
                'slug' => Str::slug($transData['title']),
                'featured_image_alt' => $enFeaturedImageAlt, // Use the EN alt tag globally
                'excerpt' => $transData['excerpt'] ?? null,
                'content' => $transData['content'] ?? null,
                'tags' => $tags,
                'faqs' => $faqs,
                'meta_title' => $transData['meta_title'] ?? null,
                'meta_description' => $transData['meta_description'] ?? null,
            ]);
        }

        return redirect('/dainely-admin-panel/blogs')->with('success', 'Blog post created successfully with automatic translation.');
    }

    public function edit(int $id)
    {
        if (!$this->flashIfSupabaseOffline('Blogs Manager')) {
            return redirect('/dainely-admin-panel/dashboard');
        }

        $post = BlogPost::with('translations')->find($id);
        if (!$post) {
            return redirect('/dainely-admin-panel/blogs')->with('error', 'Blog post not found.');
        }

        $categories = BlogCategory::all();

        return view('admin.blogs.edit', compact('post', 'categories'));
    }

    public function update(Request $request, int $id)
    {
        if (!$this->flashIfSupabaseOffline('Blogs Manager')) {
            return redirect('/dainely-admin-panel/dashboard');
        }

        $post = BlogPost::find($id);
        if (!$post) {
            return redirect('/dainely-admin-panel/blogs')->with('error', 'Blog post not found.');
        }

        $request->validate([
            'blog_category_id' => 'required|exists:blog_categories,id',
            'featured_image' => 'nullable|image|max:4096',
            'author_name' => 'nullable|string|max:255',
            'author_avatar' => 'nullable|string|max:255',
            'author_title' => 'nullable|string|max:255',
            'is_published' => 'nullable|boolean',
            'translations' => 'required|array',
        ]);

        $featuredImagePath = $post->featured_image;
        if ($request->hasFile('featured_image')) {
            if ($featuredImagePath && file_exists(public_path('images/' . $featuredImagePath))) {
                @unlink(public_path('images/' . $featuredImagePath));
            }
            $file = $request->file('featured_image');
            $filename = time() . '-' . Str::slug($request->input('translations.en.title', 'blog')) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $featuredImagePath = $filename;
        }

        $wasPublished = $post->is_published;
        $isPublished = $request->boolean('is_published');

        $post->update([
            'blog_category_id' => $request->input('blog_category_id'),
            'featured_image' => $featuredImagePath,
            'author_name' => $request->input('author_name') ?: 'Dainely Editorial',
            'author_avatar' => $request->input('author_avatar'),
            'author_title' => $request->input('author_title'),
            'is_published' => $isPublished,
            'published_at' => $isPublished ? ($wasPublished ? $post->published_at : now()) : null,
            'related_product_ids' => $request->input('related_product_ids') ?: [],
        ]);

        $submittedTranslations = $request->input('translations', []);
        $enFeaturedImageAlt = $request->input('translations.en.featured_image_alt');

        // Automatic translation for FR & DE on Update
        if (empty($submittedTranslations['fr']['title']) || empty($submittedTranslations['de']['title'])) {
            try {
                $enData = $submittedTranslations['en'] ?? [];
                $fieldsToTranslate = [
                    'title' => $enData['title'] ?? null,
                    'excerpt' => $enData['excerpt'] ?? null,
                    'content' => $enData['content'] ?? null,
                    'tags' => $enData['tags'] ?? null,
                    'meta_title' => $enData['meta_title'] ?? null,
                    'meta_description' => $enData['meta_description'] ?? null,
                ];

                $enFaqs = $enData['faqs'] ?? [];
                foreach ($enFaqs as $idx => $faq) {
                    if (!empty($faq['question']) && !empty($faq['answer'])) {
                        $fieldsToTranslate["faq_{$idx}_question"] = $faq['question'];
                        $fieldsToTranslate["faq_{$idx}_answer"] = $faq['answer'];
                    }
                }

                $translatedPayloads = app(\App\Services\ContentTranslationService::class)->translateFields($fieldsToTranslate, ['fr', 'de']);

                foreach (['fr', 'de'] as $targetLoc) {
                    if (empty($submittedTranslations[$targetLoc]['title'])) {
                        $p = $translatedPayloads[$targetLoc] ?? [];
                        
                        $targetFaqs = [];
                        foreach ($enFaqs as $idx => $faq) {
                            $targetFaqs[] = [
                                'question' => $p["faq_{$idx}_question"] ?? $faq['question'],
                                'answer' => $p["faq_{$idx}_answer"] ?? $faq['answer']
                            ];
                        }

                        $submittedTranslations[$targetLoc] = [
                            'title' => $p['title'] ?? $enData['title'],
                            'excerpt' => $p['excerpt'] ?? $enData['excerpt'] ?? null,
                            'content' => $p['content'] ?? $enData['content'] ?? null,
                            'tags' => $p['tags'] ?? $enData['tags'] ?? '',
                            'faqs' => $targetFaqs,
                            'meta_title' => $p['meta_title'] ?? $enData['meta_title'] ?? null,
                            'meta_description' => $p['meta_description'] ?? $enData['meta_description'] ?? null,
                        ];
                    }
                }
            } catch (\Throwable $e) {
                foreach (['fr', 'de'] as $targetLoc) {
                    if (empty($submittedTranslations[$targetLoc]['title'])) {
                        $submittedTranslations[$targetLoc] = $submittedTranslations['en'];
                    }
                }
            }
        }

        foreach ($submittedTranslations as $locale => $transData) {
            if (empty($transData['title'])) {
                continue;
            }

            $faqsRaw = isset($transData['faqs']) ? $transData['faqs'] : [];
            $faqs = [];
            foreach ($faqsRaw as $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $faqs[] = [
                        'question' => $faq['question'],
                        'answer' => $faq['answer']
                    ];
                }
            }

            $tagsRaw = isset($transData['tags']) ? $transData['tags'] : '';
            $tags = is_string($tagsRaw) ? array_filter(array_map('trim', explode(',', $tagsRaw))) : (is_array($tagsRaw) ? $tagsRaw : []);

            BlogPostTranslation::updateOrCreate(
                ['blog_post_id' => $post->id, 'locale' => $locale],
                [
                    'title' => $transData['title'],
                    'slug' => Str::slug($transData['title']),
                    'featured_image_alt' => $enFeaturedImageAlt, // Use the EN alt tag globally
                    'excerpt' => $transData['excerpt'] ?? null,
                    'content' => $transData['content'] ?? null,
                    'tags' => $tags,
                    'faqs' => $faqs,
                    'meta_title' => $transData['meta_title'] ?? null,
                    'meta_description' => $transData['meta_description'] ?? null,
                ]
            );
        }

        return redirect('/dainely-admin-panel/blogs')->with('success', 'Blog post updated successfully with automatic translation.');
    }

    public function destroy(int $id)
    {
        if (!$this->flashIfSupabaseOffline('Blogs Manager')) {
            return redirect('/dainely-admin-panel/blogs');
        }

        $post = BlogPost::find($id);
        if (!$post) {
            return redirect('/dainely-admin-panel/blogs')->with('error', 'Blog post not found.');
        }

        if ($post->featured_image && file_exists(public_path('images/' . $post->featured_image))) {
            @unlink(public_path('images/' . $post->featured_image));
        }

        $post->translations()->delete();
        $post->delete();

        return redirect('/dainely-admin-panel/blogs')->with('success', 'Blog post deleted successfully.');
    }
}
