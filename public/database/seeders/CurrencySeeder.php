<?php
namespace Database\Seeders;
use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        Currency::truncate();
        $currencies = [
            ['code'=>'USD','name'=>'US Dollar','symbol'=>'$','exchange_rate'=>1.000000,'is_active'=>true],
            ['code'=>'EUR','name'=>'Euro','symbol'=>'€','exchange_rate'=>0.920000,'is_active'=>true],
            ['code'=>'GBP','name'=>'British Pound','symbol'=>'£','exchange_rate'=>0.790000,'is_active'=>true],
            ['code'=>'CAD','name'=>'Canadian Dollar','symbol'=>'CA$','exchange_rate'=>1.360000,'is_active'=>true],
            ['code'=>'AUD','name'=>'Australian Dollar','symbol'=>'A$','exchange_rate'=>1.530000,'is_active'=>true],
        ];
        foreach ($currencies as $c) { Currency::create($c); }
        echo "Currencies seeded.\n";
    }
}
