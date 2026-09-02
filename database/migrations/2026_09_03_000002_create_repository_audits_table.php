<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repository_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('repo_full_name');
            $table->string('default_branch')->default('main');
            $table->string('primary_language')->nullable();
            $table->string('detected_framework')->nullable();
            $table->string('architecture_pattern')->default('Monolith');
            $table->integer('file_count')->default(0);
            $table->json('manifests')->nullable();
            $table->integer('code_health_score')->default(80);
            $table->integer('technical_debt_score')->default(20);
            $table->integer('security_score')->default(85);
            $table->json('raw_metrics')->nullable();
            $table->timestamps();

            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repository_audits');
    }
};
