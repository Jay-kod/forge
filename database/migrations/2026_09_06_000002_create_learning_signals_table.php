<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('learning_signals', function (Blueprint $table) {
            $table->id();
            $table->string('category', 80)->index();
            $table->string('signal_type', 60);
            $table->json('context_meta')->nullable();
            $table->float('value')->default(1.0);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['category', 'signal_type']);
            $table->index(['signal_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_signals');
    }
};
