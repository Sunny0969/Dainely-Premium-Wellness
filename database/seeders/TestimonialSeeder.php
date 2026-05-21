<?php
namespace Database\Seeders;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        Testimonial::truncate();
        $testimonials = [
            [
                'author_name'     => 'Sarah M.',
                'author_location' => 'Texas, USA',
                'author_avatar'   => 'testimonial-sarah.jpg',
                'rating'          => 5,
                'review_text'     => 'I have had chronic lower back pain for 3 years. After just 2 weeks with the Dainely Belt, I am finally sleeping through the night. The difference is extraordinary — I can walk my dog again without wincing.',
                'locale'          => 'en',
                'is_active'       => true,
                'is_featured'     => true,
                'sort_order'      => 1,
            ],
            [
                'author_name'     => 'Jean-Pierre D.',
                'author_location' => 'Paris, France',
                'author_avatar'   => 'testimonial-jean.jpg',
                'rating'          => 5,
                'review_text'     => 'La Ceinture Dainely est incroyable. Ma sciatique a littéralement disparu en 3 semaines. Je suis thérapeute et je recommande maintenant ce produit à mes propres patients.',
                'locale'          => 'fr',
                'is_active'       => true,
                'is_featured'     => true,
                'sort_order'      => 2,
            ],
            [
                'author_name'     => 'Klaus H.',
                'author_location' => 'Munich, Germany',
                'author_avatar'   => 'testimonial-klaus.jpg',
                'rating'          => 5,
                'review_text'     => 'Nach Jahren mit chronischen Rückenschmerzen habe ich dieses Produkt ausprobiert. Innerhalb von zwei Wochen konnte ich wieder Sport treiben. Absolute Empfehlung!',
                'locale'          => 'de',
                'is_active'       => true,
                'is_featured'     => true,
                'sort_order'      => 3,
            ],
            [
                'author_name'     => 'Maria R.',
                'author_location' => 'Madrid, Spain',
                'author_avatar'   => 'testimonial-sarah.jpg',
                'rating'          => 5,
                'review_text'     => 'I was sceptical at first but within 10 days my sciatica pain reduced by at least 70%. This belt has given me my life back. Quality is excellent — worth every penny.',
                'locale'          => 'en',
                'is_active'       => true,
                'is_featured'     => false,
                'sort_order'      => 4,
            ],
            [
                'author_name'     => 'Thomas B.',
                'author_location' => 'London, UK',
                'author_avatar'   => 'testimonial-jean.jpg',
                'rating'          => 5,
                'review_text'     => 'My physio recommended lumbar decompression and this belt is exactly what she described. After 3 weeks I have gone from barely walking to cycling 10km. Brilliant product.',
                'locale'          => 'en',
                'is_active'       => true,
                'is_featured'     => false,
                'sort_order'      => 5,
            ],
        ];
        foreach ($testimonials as $t) { Testimonial::create($t); }
        echo "Testimonials seeded: " . count($testimonials) . " reviews.\n";
    }
}
