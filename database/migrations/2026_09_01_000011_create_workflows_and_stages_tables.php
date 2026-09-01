<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->enum('mode', ['automatic', 'page_by_page'])->default('page_by_page');
            $table->enum('status', ['active', 'completed', 'paused', 'abandoned'])->default('active');
            $table->timestamps();
        });

        Schema::create('workflow_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained()->cascadeOnDelete();
            $table->string('stage_type', 50); // understanding, discovery, research, competitors, challenge, strategy, prd, architecture, package, export
            $table->unsignedInteger('order')->default(1);
            $table->enum('status', ['pending', 'active', 'completed', 'skipped', 'failed'])->default('pending');
            $table->json('content')->nullable(); // Stage output data
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['workflow_id', 'stage_type', 'version']);
        });

        Schema::create('decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('workflow_stage_id')->nullable()->constrained()->nullOnDelete();
            $table->text('question');
            $table->json('options')->nullable();
            $table->text('selected_option')->nullable();
            $table->text('rationale')->nullable();
            $table->enum('status', ['pending', 'decided', 'revised'])->default('pending');
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions');
        Schema::dropIfExists('workflow_stages');
        Schema::dropIfExists('workflows');
    }
};
