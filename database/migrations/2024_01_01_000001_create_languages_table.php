<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique(); // en, fr, de
            $table->string('name', 100); // English, French, German
            $table->string('native_name', 100); // English, Français, Deutsch
            $table->string('locale', 10); // en_US, fr_FR, de_DE
            $table->string('flag_emoji', 10)->nullable();
            $table->string('direction', 3)->default('ltr'); // ltr or rtl
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
