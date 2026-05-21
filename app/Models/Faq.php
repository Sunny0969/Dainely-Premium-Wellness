<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Faq extends Model
{
    protected $fillable = ['category','scope','scope_id','sort_order','is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function translations()
    {
        return $this->hasMany(FaqTranslation::class);
    }
    public function translation(string $locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->firstWhere('locale', $locale)
            ?? $this->translations->firstWhere('locale', 'en');
    }
}
