<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('research_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50)->default('market'); // market, competitor, technology, customer
            $table->string('status', 30)->default('pending'); // pending, running, completed, failed
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('credits_consumed')->default(0);
            $table->timestamps();
        });

        Schema::create('research_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('research_session_id')->constrained()->cascadeOnDelete();
            $table->text('url');
            $table->string('title', 500);
            $table->enum('source_type', [
                'official', 'government', 'research', 'documentation',
                'publication', 'industry', 'community', 'weak'
            ])->default('publication');
            $table->date('publication_date')->nullable();
            $table->timestamp('retrieved_at')->useCurrent();
            $table->text('content_summary')->nullable();
            $table->decimal('reliability_score', 3, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('evidence', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->text('claim');
            $table->enum('confidence', [
                'verified', 'strongly_supported', 'probable',
                'inferred', 'assumption', 'unknown', 'conflicting'
            ])->default('inferred');
            $table->decimal('confidence_score', 3, 2)->nullable();
            $table->string('category', 50)->default('market');
            $table->timestamps();
        });

        Schema::create('evidence_source_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evidence_id')->constrained()->cascadeOnDelete();
            $table->foreignId('research_source_id')->constrained()->cascadeOnDelete();
            $table->text('relevance')->nullable();
            $table->timestamps();

            $table->unique(['evidence_id', 'research_source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_source_links');
        Schema::dropIfExists('evidence');
        Schema::dropIfExists('research_sources');
        Schema::dropIfExists('research_sessions');
    }
};
