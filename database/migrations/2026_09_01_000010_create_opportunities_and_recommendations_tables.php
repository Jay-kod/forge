<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description');
            $table->string('category', 50)->default('product'); // product, market, revenue, technical, operational
            $table->enum('impact', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('difficulty', ['low', 'medium', 'high', 'extreme'])->default('medium');
            $table->enum('confidence', [
                'verified', 'strongly_supported', 'probable',
                'inferred', 'assumption'
            ])->default('inferred');
            $table->decimal('confidence_score', 3, 2)->nullable();
            $table->enum('status', ['identified', 'recommended', 'accepted', 'rejected', 'implemented'])->default('identified');
            $table->timestamps();
        });

        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('opportunity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 255);
            $table->text('description');
            $table->text('why_it_matters');
            $table->text('why_now')->nullable();
            $table->text('potential_impact');
            $table->string('difficulty', 30)->default('medium');
            $table->json('dependencies')->nullable();
            $table->json('evidence_ids')->nullable();
            $table->text('suggested_action');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'modified'])->default('pending');
            $table->text('user_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
        Schema::dropIfExists('opportunities');
    }
};
