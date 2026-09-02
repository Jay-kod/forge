<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('github_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('github_user_id')->nullable();
            $table->string('github_username')->nullable();
            $table->string('avatar_url')->nullable();
            $table->text('access_token')->nullable(); // Encrypted at rest
            $table->text('refresh_token')->nullable(); // Encrypted at rest
            $table->string('scope')->default('repo,read:user');
            $table->string('token_type')->default('bearer');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_connections');
    }
};
