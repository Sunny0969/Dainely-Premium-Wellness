<?php

namespace App\Models\Supabase;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $connection = 'supabase';
    protected $table = 'settings';
    protected $fillable = ['key', 'value'];

    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}