<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_contexts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('user_input');
            $table->string('classification', 50)->default('NEW_PRODUCT');
            $table->decimal('classification_confidence', 3, 2)->default(0.85);
            $table->json('user_understanding')->nullable();
            $table->json('business_context')->nullable();
            $table->json('product_context')->nullable();
            $table->json('geographic_context')->nullable();
            $table->json('existing_system')->nullable();
            $table->json('goals')->nullable();
            $table->timestamps();
        });

        Schema::create('context_knowledge', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_context_id')->constrained()->cascadeOnDelete();
            $table->string('field', 100);
            $table->text('value');
            $table->enum('classification', ['confirmed', 'inferred', 'assumed', 'unknown', 'conflicting'])->default('assumed');
            $table->string('source', 100)->default('user_input'); // 'user_input', 'research', 'inference'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('context_knowledge');
        Schema::dropIfExists('project_contexts');
    }
};
