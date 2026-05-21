<?php
namespace Database\Seeders;
use App\Models\DiscountCode;
use Illuminate\Database\Seeder;

class DiscountCodeSeeder extends Seeder
{
    public function run(): void
    {
        DiscountCode::truncate();
        $codes = [
            ['code'=>'WELCOME10','type'=>'percentage','value'=>10.00,'usage_limit'=>null,'is_active'=>true,'minimum_order_usd'=>0],
            ['code'=>'SAVE20','type'=>'percentage','value'=>20.00,'usage_limit'=>500,'is_active'=>true,'minimum_order_usd'=>75],
            ['code'=>'FREESHIP','type'=>'free_shipping','value'=>0.00,'usage_limit'=>null,'is_active'=>true,'minimum_order_usd'=>0],
            ['code'=>'DAINELY15','type'=>'fixed','value'=>15.00,'usage_limit'=>200,'is_active'=>true,'minimum_order_usd'=>50],
            ['code'=>'LAUNCH25','type'=>'percentage','value'=>25.00,'usage_limit'=>100,'is_active'=>true,'minimum_order_usd'=>89],
        ];
        foreach ($codes as $c) { DiscountCode::create($c); }
        echo "Discount codes seeded.\n";
    }
}
