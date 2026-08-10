<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('name'); // {"en":"Back Pain","fr":"...","de":"..."}
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('featured_image')->nullable();
            $table->string('author_name')->nullable();
            $table->string('author_avatar')->nullable();
            $table->string('author_title')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->json('related_product_ids')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blog_post_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_post_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->unique(['blog_post_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_translations');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
    }
};
