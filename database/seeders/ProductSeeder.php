<?php
namespace Database\Seeders;
use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        ProductTranslation::query()->delete();
        Product::query()->forceDelete();

        // ── Product 1: Dainely Belt ───────────────────────────────────────
        $belt = Product::create([
            'sku'                => 'DNB-001',
            'shopify_product_id' => null, // will be set by ShopifySync command
            'price_usd'          => 89.00,
            'compare_price_usd'  => 119.00,
            'is_active'          => true,
            'is_featured'        => true,
            'type'               => 'simple',
            'sort_order'         => 1,
            'main_image'         => 'images/dainely-belt-product.png',
            'gallery_images'     => json_encode([
                'images/dainely-belt-product.png',
                'images/spine-anatomy.png',
                'images/hero-lifestyle.png',
                'images/posture-edu.png',
            ]),
            'meta' => json_encode(['sizes' => ['S/M', 'L/XL', '2XL', '3XL']]),
        ]);

        ProductTranslation::create([
            'product_id'       => $belt->id,
            'locale'           => 'en',
            'name'             => 'Dainely Belt',
            'slug'             => 'dainely-belt',
            'short_description'=> 'Medical-grade lumbar decompression belt targeting sciatic nerve relief and posture correction.',
            'description'      => '<p>A medical-grade lumbar decompression belt developed with board-certified physiotherapists. Engineered to decompress vertebrae, relieve sciatic pressure, and restore natural spinal alignment — not just mask pain.</p>',
            'meta_title'       => 'Dainely Belt — Medical-Grade Lumbar Support for Back Pain & Sciatica',
            'meta_description' => 'The Dainely Belt is a clinically developed lumbar decompression belt. Relieves sciatica, corrects posture. Free shipping over $75. 30-day guarantee.',
            'benefits'         => "Decompresses lumbar vertebrae\nReduces sciatic nerve pressure\nRestores natural posture\nBreathable all-day wear\nClinically developed with spine specialists",
        ]);
        ProductTranslation::create([
            'product_id'       => $belt->id,
            'locale'           => 'fr',
            'name'             => 'Ceinture Dainely',
            'slug'             => 'ceinture-dainely',
            'short_description'=> 'Ceinture de décompression lombaire médicale ciblant le soulagement du nerf sciatique et la correction posturale.',
            'description'      => '<p>Une ceinture de décompression lombaire de qualité médicale développée avec des physiothérapeutes certifiés.</p>',
            'meta_title'       => 'Ceinture Dainely — Support Lombaire Médical contre la Sciatique',
            'meta_description' => 'La Ceinture Dainely est conçue cliniquement pour soulager la sciatique et corriger la posture. Livraison gratuite dès 75€.',
        ]);
        ProductTranslation::create([
            'product_id'       => $belt->id,
            'locale'           => 'de',
            'name'             => 'Dainely Gürtel',
            'slug'             => 'dainely-guertel',
            'short_description'=> 'Medizinischer Lendenwirbelstützen-Gürtel zur Linderung von Ischias und Haltungskorrektur.',
            'description'      => '<p>Ein medizinischer Lendenwirbelgürtel, entwickelt mit zertifizierten Physiotherapeuten.</p>',
            'meta_title'       => 'Dainely Gürtel — Medizinische Lendenwirbel-Unterstützung',
            'meta_description' => 'Der Dainely Gürtel wurde klinisch entwickelt, um Ischias zu lindern und die Haltung zu korrigieren. Kostenloser Versand ab 75€.',
        ]);

        // ── Product 2: Daily Relief System ───────────────────────────────
        $system = Product::create([
            'sku'                => 'DRS-001',
            'shopify_product_id' => null,
            'price_usd'          => 149.00,
            'compare_price_usd'  => 189.00,
            'is_active'          => true,
            'is_featured'        => true,
            'type'               => 'bundle',
            'sort_order'         => 2,
            'main_image'         => 'images/daily-relief-system.png',
            'gallery_images'     => json_encode(['images/daily-relief-system.png']),
            'meta'               => json_encode(['includes' => ['Dainely Belt','Foam Roller','Resistance Bands','Recovery Guide']]),
        ]);
        ProductTranslation::create([
            'product_id'       => $system->id,
            'locale'           => 'en',
            'name'             => 'Daily Relief System',
            'slug'             => 'daily-relief-system',
            'short_description'=> 'Complete wellness protocol: Dainely Belt + foam roller + resistance bands + recovery guide. Save $40.',
            'description'      => '<p>The complete back pain recovery system combining the Dainely Belt with targeted exercise tools and a comprehensive recovery guide written by our physiotherapy team.</p>',
            'meta_title'       => 'Daily Relief System — Complete Back Pain Wellness Bundle | Dainely',
            'meta_description' => 'Complete wellness bundle: Dainely Belt + foam roller + resistance bands + recovery guide. Save $40 vs buying separately. Free shipping.',
        ]);
        ProductTranslation::create([
            'product_id'       => $system->id,
            'locale'           => 'fr',
            'name'             => 'Système de Soulagement Quotidien',
            'slug'             => 'systeme-soulagement-quotidien',
            'short_description'=> 'Protocole bien-être complet: Ceinture Dainely + rouleau mousse + bandes de résistance + guide de récupération.',
            'description'      => '<p>Le système complet de récupération contre la douleur dorsale.</p>',
            'meta_title'       => 'Système Soulagement Quotidien — Kit Bien-être Complet | Dainely',
            'meta_description' => 'Kit bien-être complet avec ceinture Dainely, rouleau mousse et guide de récupération.',
        ]);
        ProductTranslation::create([
            'product_id'       => $system->id,
            'locale'           => 'de',
            'name'             => 'Tägliches Linderungs-System',
            'slug'             => 'taegliches-linderungs-system',
            'short_description'=> 'Vollständiges Wellness-Protokoll: Dainely Gürtel + Schaumstoffrolle + Widerstandsbänder + Erholungsguide.',
            'description'      => '<p>Das vollständige Rückenrckenschmerz-Erholungssystem.</p>',
            'meta_title'       => 'Tägliches Linderungs-System — Komplettes Wellness-Bundle | Dainely',
            'meta_description' => 'Komplettes Wellness-Bundle mit Dainely Gürtel und Erholungsguide. Kostenloser Versand ab 75€.',
        ]);

        echo "Products seeded: 2 products, 6 translations.\n";
    }
}
