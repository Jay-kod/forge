<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 3);
            $table->string('country_name', 100);
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('currency_code', 3)->default('USD');
            $table->json('regulatory_notes')->nullable();
            $table->json('payment_methods')->nullable();
            $table->timestamps();
        });

        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('target_geography', 255)->nullable();
            $table->string('tam_estimate', 100)->nullable();
            $table->string('sam_estimate', 100)->nullable();
            $table->string('som_estimate', 100)->nullable();
            $table->json('key_drivers')->nullable();
            $table->json('barriers_to_entry')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markets');
        Schema::dropIfExists('locations');
    }
};
