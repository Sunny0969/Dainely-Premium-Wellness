<?php

namespace App\Models\Catalog;

use App\Contracts\SearchableEntity;
use App\Support\ContentCatalog;
use Illuminate\Database\Eloquent\Model;

/**
 * Virtual searchable entity for static blog posts (ContentCatalog).
 */
class BlogPost extends Model implements SearchableEntity
{
    public $timestamps = false;

    protected $guarded = [];

    public static function findCatalog(int $id): ?self
    {
        $data = ContentCatalog::blogById($id);
        if (! $data) {
            return null;
        }

        $model = new self([
            'id' => $data['id'],
            'slug' => $data['slug'],
            'title' => $data['title'],
        ]);
        $model->exists = true;

        return $model;
    }

    public function getTranslatedTitle(?string $locale = null): string
    {
        return (string) $this->title;
    }

    public function getPlainTextContent(?string $locale = null): string
    {
        $slug = (string) ($this->slug ?? '');

        return trim(implode(' ', array_filter([
            (string) $this->title,
            str_replace('-', ' ', $slug),
            'blog',
            'article',
            'dainely',
        ])));
    }

    public function getSearchKeywords(?string $locale = null): ?string
    {
        $slug = (string) ($this->slug ?? '');

        return implode(',', array_filter([$slug, 'blog', 'article']));
    }
}
