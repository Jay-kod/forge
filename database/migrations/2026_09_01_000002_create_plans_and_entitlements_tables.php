<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->decimal('price_monthly', 8, 2)->nullable();
            $table->decimal('price_annual', 8, 2)->nullable();
            $table->unsignedInteger('credits_monthly')->default(25);
            $table->string('stripe_price_id_monthly')->nullable();
            $table->string('stripe_price_id_annual')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable();
            $table->timestamps();
        });

        Schema::create('entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->string('capability', 100);
            $table->string('value', 100);
            $table->timestamps();

            $table->unique(['plan_id', 'capability']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entitlements');
        Schema::dropIfExists('plans');
    }
};
