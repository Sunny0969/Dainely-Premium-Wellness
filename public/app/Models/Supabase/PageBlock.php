<?php

namespace App\Models\Supabase;

use App\Traits\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PageBlock extends Model
{
    use HasLocalizedContent;

    protected $connection = 'supabase';

    protected $table = 'page_blocks';

    protected $fillable = [
        'blockable_type',
        'blockable_id',
        'locale',
        'block_type',
        'title',
        'content',
        'sort_order',
        'visible',
        'display_position',
        'is_global',
    ];

    protected $casts = [
        'visible' => 'boolean',
        'is_global' => 'boolean',
    ];

    public function blockable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeVisible($query)
    {
        // Postgres boolean — avoid integer 1 binding with emulate prepares.
        return $query->whereRaw('visible = true');
    }

    protected static function booted(): void
    {
        $reindex = function (PageBlock $block) {
            $type = $block->blockable_type;
            $id = (int) $block->blockable_id;
            $locale = $block->locale ?: null;

            if (! $id || ! in_array($type, [LandingPage::class, Product::class], true)) {
                return;
            }

            dispatch(function () use ($type, $id, $locale) {
                try {
                    app(\App\Services\SearchService::class)->queueIndex($type, $id, $locale);
                } catch (\Throwable $e) {
                    logger()->warning('Deferred page-block search index failed: '.$e->getMessage());
                }
            })->afterResponse();
        };

        static::saved($reindex);
        static::deleted($reindex);
    }
}
