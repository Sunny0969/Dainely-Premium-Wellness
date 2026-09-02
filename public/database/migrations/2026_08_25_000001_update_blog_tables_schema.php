<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_post_translations', function (Blueprint $table) {
            $table->string('featured_image_alt')->nullable()->after('slug');
            $table->json('tags')->nullable()->after('content');
            $table->json('faqs')->nullable()->after('tags');
        });

        // Seed default categories
        $categories = [
            ['key' => 'movement-mobility', 'name' => json_encode(['en' => 'Movement & Mobility', 'fr' => 'Mouvement et mobilité', 'de' => 'Bewegung & Mobilität'])],
            ['key' => 'posture-alignment', 'name' => json_encode(['en' => 'Posture & Alignment', 'fr' => 'Posture et alignement', 'de' => 'Haltung & Ausrichtung'])],
            ['key' => 'back-core-support', 'name' => json_encode(['en' => 'Back & Core Support', 'fr' => 'Soutien du dos et du tronc', 'de' => 'Rücken- & Rumpfstütze'])],
            ['key' => 'neck-upper-body', 'name' => json_encode(['en' => 'Neck & Upper Body', 'fr' => 'Cou et haut du corps', 'de' => 'Nacken & Oberkörper'])],
            ['key' => 'recovery-relaxation', 'name' => json_encode(['en' => 'Recovery & Relaxation', 'fr' => 'Récupération et relaxation', 'de' => 'Erholung & Entspannung'])],
            ['key' => 'active-living-50', 'name' => json_encode(['en' => 'Active Living 50+', 'fr' => 'Vie active 50+', 'de' => 'Aktives Leben 50+'])],
            ['key' => 'product-guides', 'name' => json_encode(['en' => 'Product Guides', 'fr' => 'Guides produits', 'de' => 'Produkt-Ratgeber'])],
        ];

        foreach ($categories as $cat) {
            DB::table('blog_categories')->updateOrInsert(['key' => $cat['key']], $cat);
        }
    }

    public function down(): void
    {
        Schema::table('blog_post_translations', function (Blueprint $table) {
            $table->dropColumn(['featured_image_alt', 'tags', 'faqs']);
        });
    }
};
