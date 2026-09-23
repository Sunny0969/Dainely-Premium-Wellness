<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class BackupService
{
    public static function run()
    {
        $data = [
            'timestamp' => now()->toIso8601String(),
            'default_db' => [],
            'supabase_db' => []
        ];

        // Fetch from Default DB
        $defaultTables = [
            'blog_posts' => \App\Models\BlogPost::all()->toArray(),
            'blog_post_translations' => \App\Models\BlogPostTranslation::all()->toArray(),
            'blog_categories' => \App\Models\BlogCategory::all()->toArray(),
            'education_pages' => \App\Models\Catalog\EducationPage::all()->toArray(),
        ];
        $data['default_db'] = $defaultTables;

        // Fetch from Supabase DB
        if (\App\Support\SupabaseDb::available()) {
            $data['supabase_db'] = \App\Support\SupabaseDb::run(function () {
                return [
                    'products' => \App\Models\Supabase\Product::all()->toArray(),
                    'product_contents' => \App\Models\Supabase\ProductContent::all()->toArray(),
                    'page_blocks' => \App\Models\Supabase\PageBlock::all()->toArray(),
                    'landing_pages' => \App\Models\Supabase\LandingPage::all()->toArray(),
                    'faqs' => \App\Models\Supabase\Faq::all()->toArray(),
                    'product_knowledge_signals' => \App\Models\Supabase\ProductKnowledgeSignal::all()->toArray(),
                    'related_contents' => \App\Models\Supabase\RelatedContent::all()->toArray(),
                    'product_bundles' => \App\Models\Supabase\ProductBundle::all()->toArray(),
                    'product_bundle_items' => \App\Models\Supabase\ProductBundleItem::all()->toArray(),
                ];
            }, []);
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $backupPath = base_path('backups');
        if (!File::exists($backupPath)) {
            File::makeDirectory($backupPath, 0755, true);
        }

        $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '.json';
        File::put($backupPath . DIRECTORY_SEPARATOR . $filename, $json);

        return $filename;
    }
}
