<?php
namespace Database\Seeders;
use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::truncate();
        $languages = [
            ['code'=>'en','name'=>'English','native_name'=>'English','locale'=>'en_US','flag_emoji'=>'🇺🇸','direction'=>'ltr','is_active'=>true,'is_default'=>true,'sort_order'=>1],
            ['code'=>'fr','name'=>'French','native_name'=>'Français','locale'=>'fr_FR','flag_emoji'=>'🇫🇷','direction'=>'ltr','is_active'=>true,'is_default'=>false,'sort_order'=>2],
            ['code'=>'de','name'=>'German','native_name'=>'Deutsch','locale'=>'de_DE','flag_emoji'=>'🇩🇪','direction'=>'ltr','is_active'=>true,'is_default'=>false,'sort_order'=>3],
        ];
        foreach ($languages as $lang) { Language::create($lang); }
        echo "Languages seeded.\n";
    }
}
