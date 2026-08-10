<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BlogCategory extends Model
{
    protected $fillable = ['key','name'];
    protected $casts = ['name' => 'array'];
    public function posts() { return $this->hasMany(BlogPost::class); }
}
