<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Testimonial extends Model
{
    protected $fillable = [
        'product_id','author_name','author_location','author_avatar',
        'rating','review_text','locale','is_active','is_featured','sort_order',
    ];
    protected $casts = ['is_active'=>'boolean','is_featured'=>'boolean'];
    public function product() { return $this->belongsTo(Product::class); }
}
