<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50); // 'prd', 'architecture', 'agents_md', 'claude_md', 'master_prompt', 'executive_summary'
            $table->string('title', 255);
            $table->longText('content');
            $table->unsignedInteger('version')->default(1);
            $table->enum('status', ['draft', 'approved', 'superseded'])->default('draft');
            $table->json('evidence_ids')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_documents');
    }
};
