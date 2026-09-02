<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BlogPostTranslation extends Model
{
    protected $fillable = [
        'blog_post_id','locale','title','slug',
        'featured_image_alt','excerpt','content','tags','faqs',
        'meta_title','meta_description',
    ];
    protected $casts = [
        'tags' => 'array',
        'faqs' => 'array',
    ];
    public function blogPost() { return $this->belongsTo(BlogPost::class); }
}
