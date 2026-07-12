<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Align Phase 2 Supabase tables to the spec.
 * Products table remains "dainely_products" (client DB already has "products").
 */
return new class extends Migration
{
    protected string $conn = 'supabase';

    public function up(): void
    {
        $this->alignPageBlocks();
        $this->alignFaqs();
        $this->alignKnowledgeSignals();
        $this->alignRelatedContent();
        $this->alignAiSchemaCache();
        $this->alignLandingPages();
        $this->alignBundles();
        $this->alignAnalytics();
        $this->alignUserActivityLog();
        $this->alignSearchIndex();
        $this->alignWebhookLogs();
        $this->alignRecommendationRules();
    }

    public function down(): void
    {
        // Non-destructive reverse omitted — alignment is one-way toward Phase 2 spec.
    }

    protected function hasColumn(string $table, string $column): bool
    {
        return Schema::connection($this->conn)->hasColumn($table, $column);
    }

    protected function alignPageBlocks(): void
    {
        if (! Schema::connection($this->conn)->hasTable('page_blocks')) {
            return;
        }

        Schema::connection($this->conn)->table('page_blocks', function (Blueprint $table) {
            if (! $this->hasColumn('page_blocks', 'title')) {
                $table->string('title')->nullable()->after('locale');
            }
        });

        if ($this->hasColumn('page_blocks', 'type') && ! $this->hasColumn('page_blocks', 'block_type')) {
            DB::connection($this->conn)->statement('ALTER TABLE page_blocks RENAME COLUMN type TO block_type');
        }

        if ($this->hasColumn('page_blocks', 'is_active') && ! $this->hasColumn('page_blocks', 'visible')) {
            DB::connection($this->conn)->statement('ALTER TABLE page_blocks RENAME COLUMN is_active TO visible');
        }

        // Prefer text content per spec (keep JSON castable as text)
        DB::connection($this->conn)->statement('ALTER TABLE page_blocks ALTER COLUMN content TYPE text USING content::text');
        DB::connection($this->conn)->statement('ALTER TABLE page_blocks ALTER COLUMN content DROP NOT NULL');
    }

    protected function alignFaqs(): void
    {
        if (! Schema::connection($this->conn)->hasTable('faqs')) {
            return;
        }

        Schema::connection($this->conn)->table('faqs', function (Blueprint $table) {
            if (! $this->hasColumn('faqs', 'locale')) {
                $table->string('locale', 5)->default('en')->index();
            }
            if (! $this->hasColumn('faqs', 'question')) {
                $table->text('question')->nullable();
            }
            if (! $this->hasColumn('faqs', 'answer')) {
                $table->text('answer')->nullable();
            }
            if (! $this->hasColumn('faqs', 'approved')) {
                $table->boolean('approved')->default(true);
            }
        });

        // Migrate translations → flat FAQ rows (Phase 2 shape)
        if (Schema::connection($this->conn)->hasTable('faq_translations')) {
            $rows = DB::connection($this->conn)->select('
                SELECT f.id, f.faqable_type, f.faqable_id, f.sort_order, f.is_active,
                       t.locale, t.question, t.answer
                FROM faqs f
                INNER JOIN faq_translations t ON t.faq_id = f.id
            ');

            foreach ($rows as $row) {
                // Update the base row for first locale; insert extras for additional locales
                $existing = DB::connection($this->conn)->table('faqs')->where('id', $row->id)->first();
                if ($existing && empty($existing->question)) {
                    DB::connection($this->conn)->table('faqs')->where('id', $row->id)->update([
                        'locale'   => $row->locale,
                        'question' => $row->question,
                        'answer'   => $row->answer,
                        'approved' => (bool) ($row->is_active ?? true),
                    ]);
                } else {
                    DB::connection($this->conn)->table('faqs')->insert([
                        'faqable_type' => $row->faqable_type,
                        'faqable_id'   => $row->faqable_id,
                        'locale'       => $row->locale,
                        'question'     => $row->question,
                        'answer'       => $row->answer,
                        'sort_order'   => $row->sort_order ?? 0,
                        'approved'     => (bool) ($row->is_active ?? true),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }
            }

            Schema::connection($this->conn)->dropIfExists('faq_translations');
        }

        if ($this->hasColumn('faqs', 'is_active') && $this->hasColumn('faqs', 'approved')) {
            DB::connection($this->conn)->statement('UPDATE faqs SET approved = COALESCE(is_active, true) WHERE approved IS NULL OR approved = true');
            // keep is_active for safety or drop
            Schema::connection($this->conn)->table('faqs', function (Blueprint $table) {
                if ($this->hasColumn('faqs', 'is_active')) {
                    $table->dropColumn('is_active');
                }
                if ($this->hasColumn('faqs', 'category')) {
                    $table->dropColumn('category');
                }
            });
        }

        DB::connection($this->conn)->statement('CREATE INDEX IF NOT EXISTS faqs_faqable_locale_idx ON faqs (faqable_type, faqable_id, locale)');
    }

    protected function alignKnowledgeSignals(): void
    {
        if (! Schema::connection($this->conn)->hasTable('product_knowledge_signals')) {
            return;
        }

        Schema::connection($this->conn)->table('product_knowledge_signals', function (Blueprint $table) {
            if (! $this->hasColumn('product_knowledge_signals', 'speaker_type')) {
                $table->string('speaker_type')->default('expert');
            }
            if (! $this->hasColumn('product_knowledge_signals', 'keywords')) {
                $table->json('keywords')->nullable();
            }
            if (! $this->hasColumn('product_knowledge_signals', 'confidence')) {
                $table->float('confidence')->default(1.0);
            }
            if (! $this->hasColumn('product_knowledge_signals', 'embedding_id')) {
                $table->string('embedding_id')->nullable();
            }
            if (! $this->hasColumn('product_knowledge_signals', 'approved')) {
                $table->boolean('approved')->default(false);
            }
        });

        if ($this->hasColumn('product_knowledge_signals', 'is_approved')) {
            DB::connection($this->conn)->statement('UPDATE product_knowledge_signals SET approved = COALESCE(is_approved, false)');
            Schema::connection($this->conn)->table('product_knowledge_signals', function (Blueprint $table) {
                $table->dropColumn('is_approved');
            });
        }
    }

    protected function alignRelatedContent(): void
    {
        if (! Schema::connection($this->conn)->hasTable('related_content')) {
            return;
        }

        if ($this->hasColumn('related_content', 'sort_order') && ! $this->hasColumn('related_content', 'display_order')) {
            DB::connection($this->conn)->statement('ALTER TABLE related_content RENAME COLUMN sort_order TO display_order');
        }
    }

    protected function alignAiSchemaCache(): void
    {
        if (! Schema::connection($this->conn)->hasTable('ai_schema_cache')) {
            return;
        }

        Schema::connection($this->conn)->table('ai_schema_cache', function (Blueprint $table) {
            if (! $this->hasColumn('ai_schema_cache', 'cacheable_type')) {
                $table->string('cacheable_type')->nullable()->index();
            }
            if (! $this->hasColumn('ai_schema_cache', 'cacheable_id')) {
                $table->unsignedBigInteger('cacheable_id')->nullable()->index();
            }
            if (! $this->hasColumn('ai_schema_cache', 'schema_data')) {
                $table->json('schema_data')->nullable();
            }
            if (! $this->hasColumn('ai_schema_cache', 'schema_version')) {
                $table->string('schema_version')->default('1.0');
            }
            if (! $this->hasColumn('ai_schema_cache', 'generated_at')) {
                $table->timestamp('generated_at')->nullable();
            }
        });

        if ($this->hasColumn('ai_schema_cache', 'schemaable_type')) {
            DB::connection($this->conn)->statement('UPDATE ai_schema_cache SET cacheable_type = schemaable_type WHERE cacheable_type IS NULL');
            DB::connection($this->conn)->statement('UPDATE ai_schema_cache SET cacheable_id = schemaable_id WHERE cacheable_id IS NULL');
        }
        if ($this->hasColumn('ai_schema_cache', 'schema_json')) {
            DB::connection($this->conn)->statement('UPDATE ai_schema_cache SET schema_data = schema_json WHERE schema_data IS NULL');
        }
        DB::connection($this->conn)->statement('UPDATE ai_schema_cache SET generated_at = COALESCE(generated_at, created_at, NOW())');

        // Drop old unique + columns if present
        try {
            DB::connection($this->conn)->statement('ALTER TABLE ai_schema_cache DROP CONSTRAINT IF EXISTS schema_cache_unique');
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::connection($this->conn)->table('ai_schema_cache', function (Blueprint $table) {
            if ($this->hasColumn('ai_schema_cache', 'schemaable_type')) {
                $table->dropColumn(['schemaable_type', 'schemaable_id']);
            }
            if ($this->hasColumn('ai_schema_cache', 'schema_json')) {
                $table->dropColumn('schema_json');
            }
            if ($this->hasColumn('ai_schema_cache', 'schema_type')) {
                $table->dropColumn('schema_type');
            }
            if ($this->hasColumn('ai_schema_cache', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });

        try {
            DB::connection($this->conn)->statement('CREATE UNIQUE INDEX IF NOT EXISTS ai_schema_cache_unique ON ai_schema_cache (cacheable_type, cacheable_id, locale)');
        } catch (\Throwable $e) {
            // ignore duplicates
        }
    }

    protected function alignLandingPages(): void
    {
        if (! Schema::connection($this->conn)->hasTable('landing_pages')) {
            return;
        }

        Schema::connection($this->conn)->table('landing_pages', function (Blueprint $table) {
            if (! $this->hasColumn('landing_pages', 'canonical_url')) {
                $table->string('canonical_url')->nullable();
            }
            if (! $this->hasColumn('landing_pages', 'published')) {
                $table->boolean('published')->default(false);
            }
        });

        if ($this->hasColumn('landing_pages', 'is_active')) {
            DB::connection($this->conn)->statement('UPDATE landing_pages SET published = COALESCE(is_active, false)');
            Schema::connection($this->conn)->table('landing_pages', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }

    protected function alignBundles(): void
    {
        if (! Schema::connection($this->conn)->hasTable('product_bundles')) {
            return;
        }

        Schema::connection($this->conn)->table('product_bundles', function (Blueprint $table) {
            if (! $this->hasColumn('product_bundles', 'bundle_shopify_product_id')) {
                $table->string('bundle_shopify_product_id')->nullable();
            }
            if (! $this->hasColumn('product_bundles', 'locale')) {
                $table->string('locale', 5)->default('en');
            }
            if (! $this->hasColumn('product_bundles', 'title')) {
                $table->string('title')->nullable();
            }
            if (! $this->hasColumn('product_bundles', 'description')) {
                $table->text('description')->nullable();
            }
        });

        if ($this->hasColumn('product_bundles', 'shopify_product_id')) {
            DB::connection($this->conn)->statement('UPDATE product_bundles SET bundle_shopify_product_id = shopify_product_id WHERE bundle_shopify_product_id IS NULL');
        }
        if ($this->hasColumn('product_bundles', 'handle')) {
            DB::connection($this->conn)->statement("UPDATE product_bundles SET title = COALESCE(title, handle, 'Bundle')");
        }

        // Drop non-spec columns carefully
        Schema::connection($this->conn)->table('product_bundles', function (Blueprint $table) {
            foreach (['shopify_product_id', 'handle', 'price_usd', 'is_active'] as $col) {
                if ($this->hasColumn('product_bundles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::connection($this->conn)->hasTable('product_bundle_items') && $this->hasColumn('product_bundle_items', 'variant_id')) {
            Schema::connection($this->conn)->table('product_bundle_items', function (Blueprint $table) {
                $table->dropColumn('variant_id');
            });
        }

        try {
            DB::connection($this->conn)->statement('CREATE UNIQUE INDEX IF NOT EXISTS product_bundles_bundle_locale_uidx ON product_bundles (bundle_shopify_product_id, locale)');
        } catch (\Throwable $e) {
        }
    }

    protected function alignAnalytics(): void
    {
        if (! Schema::connection($this->conn)->hasTable('analytics_events')) {
            return;
        }

        Schema::connection($this->conn)->table('analytics_events', function (Blueprint $table) {
            if (! $this->hasColumn('analytics_events', 'event_data')) {
                $table->json('event_data')->nullable();
            }
            if (! $this->hasColumn('analytics_events', 'user_id')) {
                $table->string('user_id')->nullable();
            }
            if (! $this->hasColumn('analytics_events', 'occurred_at')) {
                $table->timestamp('occurred_at')->nullable();
            }
        });

        if ($this->hasColumn('analytics_events', 'payload')) {
            DB::connection($this->conn)->statement('UPDATE analytics_events SET event_data = payload WHERE event_data IS NULL');
        }
        DB::connection($this->conn)->statement('UPDATE analytics_events SET occurred_at = COALESCE(occurred_at, created_at, NOW())');
    }

    protected function alignUserActivityLog(): void
    {
        if (! Schema::connection($this->conn)->hasTable('user_activity_log')) {
            return;
        }

        Schema::connection($this->conn)->table('user_activity_log', function (Blueprint $table) {
            if (! $this->hasColumn('user_activity_log', 'visitor_id')) {
                $table->string('visitor_id')->nullable()->index();
            }
            if (! $this->hasColumn('user_activity_log', 'user_id')) {
                $table->string('user_id')->nullable();
            }
            if (! $this->hasColumn('user_activity_log', 'event_type')) {
                $table->string('event_type')->nullable()->index();
            }
            if (! $this->hasColumn('user_activity_log', 'item_type')) {
                $table->string('item_type')->nullable()->index();
            }
            if (! $this->hasColumn('user_activity_log', 'item_id')) {
                $table->unsignedBigInteger('item_id')->nullable()->index();
            }
            if (! $this->hasColumn('user_activity_log', 'context')) {
                $table->json('context')->nullable();
            }
        });

        if ($this->hasColumn('user_activity_log', 'action')) {
            DB::connection($this->conn)->statement('UPDATE user_activity_log SET event_type = COALESCE(event_type, action)');
        }
        if ($this->hasColumn('user_activity_log', 'details')) {
            DB::connection($this->conn)->statement('UPDATE user_activity_log SET context = COALESCE(context, details)');
        }
        if ($this->hasColumn('user_activity_log', 'session_id')) {
            DB::connection($this->conn)->statement("UPDATE user_activity_log SET visitor_id = COALESCE(visitor_id, session_id, 'unknown')");
        }
    }

    protected function alignSearchIndex(): void
    {
        if (! Schema::connection($this->conn)->hasTable('search_index')) {
            return;
        }

        Schema::connection($this->conn)->table('search_index', function (Blueprint $table) {
            if (! $this->hasColumn('search_index', 'body_plain')) {
                $table->text('body_plain')->nullable();
            }
            if (! $this->hasColumn('search_index', 'keywords')) {
                $table->text('keywords')->nullable();
            }
        });

        if ($this->hasColumn('search_index', 'content')) {
            DB::connection($this->conn)->statement('UPDATE search_index SET body_plain = COALESCE(body_plain, content)');
        }

        // Ensure tsv column + GIN
        $hasTsv = DB::connection($this->conn)->selectOne("
            SELECT 1 AS ok FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = 'search_index' AND column_name = 'tsv'
        ");
        if (! $hasTsv) {
            if ($this->hasColumn('search_index', 'search_vector')) {
                DB::connection($this->conn)->statement('ALTER TABLE search_index RENAME COLUMN search_vector TO tsv');
            } else {
                DB::connection($this->conn)->statement('ALTER TABLE search_index ADD COLUMN tsv tsvector');
            }
        }

        DB::connection($this->conn)->statement('CREATE INDEX IF NOT EXISTS search_idx ON search_index USING GIN(tsv)');
    }

    protected function alignWebhookLogs(): void
    {
        if (! Schema::connection($this->conn)->hasTable('webhook_logs')) {
            return;
        }

        if ($this->hasColumn('webhook_logs', 'error_message') && ! $this->hasColumn('webhook_logs', 'error')) {
            DB::connection($this->conn)->statement('ALTER TABLE webhook_logs RENAME COLUMN error_message TO error');
        }
    }

    protected function alignRecommendationRules(): void
    {
        if (! Schema::connection($this->conn)->hasTable('recommendation_rules')) {
            return;
        }

        // Rebuild to Phase 2 morph shape if old trigger-based schema
        if ($this->hasColumn('recommendation_rules', 'trigger_type')) {
            Schema::connection($this->conn)->drop('recommendation_rules');
            Schema::connection($this->conn)->create('recommendation_rules', function (Blueprint $table) {
                $table->id();
                $table->string('rule_type');
                $table->string('source_item_type');
                $table->unsignedBigInteger('source_item_id');
                $table->string('recommended_item_type');
                $table->unsignedBigInteger('recommended_item_id');
                $table->float('score')->default(1.0);
                $table->timestamps();

                $table->index(['source_item_type', 'source_item_id']);
                $table->index(['recommended_item_type', 'recommended_item_id']);
            });
        }
    }
};
