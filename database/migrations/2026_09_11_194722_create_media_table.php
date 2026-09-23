<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('s3');
            $table->string('storage_path');
            $table->string('original_path');
            $table->string('filename');
            $table->string('mime_type');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->bigInteger('size_bytes')->nullable();
            $table->jsonb('responsive_widths')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('media');
    }
};