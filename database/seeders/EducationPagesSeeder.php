<?php

namespace Database\Seeders;

use App\Models\Catalog\EducationPage;
use App\Support\ContentCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EducationPagesSeeder extends Seeder
{
    public function run(): void
    {
        $pages = ContentCatalog::educationPages();

        foreach ($pages as $page) {
            EducationPage::updateOrCreate(
                ['slug' => $page['slug']],
                [
                    'id' => $page['id'], // Keep ID same for legacy page blocks & FAQs
                    'title' => $page['title'],
                    'hero_title' => $page['title'],
                    'is_active' => true,
                ]
            );
        }
        
        // Reset auto increment
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("SELECT setval('education_pages_id_seq', (SELECT MAX(id) FROM education_pages))");
        }
    }
}
