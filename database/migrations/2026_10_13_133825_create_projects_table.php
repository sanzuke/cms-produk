<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category', 20)->index();
            $table->string('commodity', 30)->nullable()->index();
            $table->string('work_area', 30)->nullable();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->string('client_name')->nullable();
            $table->boolean('show_client')->default(false);
            $table->string('location')->nullable();
            $table->boolean('show_location')->default(false);
            $table->text('challenge')->nullable();
            $table->text('approach')->nullable();
            $table->text('result')->nullable();
            $table->string('specs')->nullable();
            $table->string('cover')->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('featured')->default(false)->index();
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
