<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discoveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('verdict', [
                'build_as_proposed',
                'build_with_modifications',
                'consider_alternative',
                'do_not_build_yet'
            ])->default('build_with_modifications');
            $table->text('summary');
            $table->text('rationale')->nullable();
            $table->timestamps();
        });

        Schema::create('competitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('url')->nullable();
            $table->text('description')->nullable();
            $table->enum('category', ['direct', 'indirect', 'adjacent'])->default('direct');
            $table->json('strengths')->nullable();
            $table->json('weaknesses')->nullable();
            $table->json('pricing')->nullable();
            $table->string('target_market', 255)->nullable();
            $table->text('differentiation')->nullable();
            $table->json('source_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitors');
        Schema::dropIfExists('discoveries');
    }
};
