<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('balance')->default(0);
            $table->unsignedInteger('lifetime_granted')->default(0);
            $table->unsignedInteger('lifetime_consumed')->default(0);
            $table->timestamps();
        });

        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_account_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['grant', 'consumption', 'reservation', 'release', 'refund', 'expiry', 'purchase']);
            $table->integer('amount'); // Positive for grant/refund, negative for consumption
            $table->unsignedInteger('balance_after');
            $table->string('reference_type', 50)->nullable();
            $table->string('reference_id', 100)->nullable();
            $table->string('description', 255)->nullable();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('credit_accounts');
    }
};
