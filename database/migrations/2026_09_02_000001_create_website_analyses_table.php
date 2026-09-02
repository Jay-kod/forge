<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('status', 50)->default('completed');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->json('headings')->nullable();
            $table->json('performance_hints')->nullable();
            $table->integer('ux_score')->default(75);
            $table->integer('seo_score')->default(80);
            $table->integer('conversion_score')->default(70);
            $table->json('conversion_findings')->nullable();
            $table->json('recommendations')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_analyses');
    }
};
