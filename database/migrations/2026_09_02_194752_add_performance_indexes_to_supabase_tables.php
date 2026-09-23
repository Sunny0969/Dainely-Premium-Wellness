<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection("supabase")->table("products", function (Blueprint $table) {
            $table->index("shopify_product_id");
            $table->index("handle");
        });

        Schema::connection("supabase")->table("product_content", function (Blueprint $table) {
            $table->index(["product_id", "locale"]);
        });

        Schema::connection("supabase")->table("page_blocks", function (Blueprint $table) {
            $table->index(["blockable_type", "blockable_id", "sort_order"]);
            $table->index(["blockable_type", "blockable_id", "locale"]);
        });

        Schema::connection("supabase")->table("faqs", function (Blueprint $table) {
            $table->index(["faqable_type", "faqable_id", "locale"]);
            $table->index(["faqable_type", "faqable_id", "sort_order"]);
        });
        
        Schema::connection("supabase")->table("landing_pages", function (Blueprint $table) {
            $table->index(["slug", "locale"]);
        });
    }

    public function down(): void
    {
        Schema::connection("supabase")->table("products", function (Blueprint $table) {
            $table->dropIndex(["shopify_product_id"]);
            $table->dropIndex(["handle"]);
        });

        Schema::connection("supabase")->table("product_content", function (Blueprint $table) {
            $table->dropIndex(["product_id", "locale"]);
        });

        Schema::connection("supabase")->table("page_blocks", function (Blueprint $table) {
            $table->dropIndex(["blockable_type", "blockable_id", "sort_order"]);
            $table->dropIndex(["blockable_type", "blockable_id", "locale"]);
        });

        Schema::connection("supabase")->table("faqs", function (Blueprint $table) {
            $table->dropIndex(["faqable_type", "faqable_id", "locale"]);
            $table->dropIndex(["faqable_type", "faqable_id", "sort_order"]);
        });
        
        Schema::connection("supabase")->table("landing_pages", function (Blueprint $table) {
            $table->dropIndex(["slug", "locale"]);
        });
    }
};
