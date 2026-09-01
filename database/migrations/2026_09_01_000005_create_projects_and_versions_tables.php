<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->string('classification', 50)->default('NEW_PRODUCT');
            $table->string('status', 30)->default('active'); // active, archived, completed
            $table->enum('workflow_mode', ['automatic', 'page_by_page'])->default('page_by_page');
            $table->string('current_stage', 50)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('project_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->json('snapshot'); // Full snapshot of all state at this version
            $table->string('created_by', 50)->default('system'); // 'user', 'system', 'regeneration'
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['project_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_versions');
        Schema::dropIfExists('projects');
    }
};
