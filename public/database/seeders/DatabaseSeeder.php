<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LanguageSeeder::class,
            CurrencySeeder::class,
            ProductSeeder::class,
            DiscountCodeSeeder::class,
            FaqSeeder::class,
            TestimonialSeeder::class,
        ]);
    }
}
