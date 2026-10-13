<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('article')->index();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('excerpt', 500)->nullable();
            $table->longText('body')->nullable();
            $table->string('cover')->nullable();
            $table->string('category', 60)->nullable()->index();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 300)->nullable();
            $table->string('status', 20)->default('draft')->index();
            $table->timestamp('publish_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
