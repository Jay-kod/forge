<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Organizations Table
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('plan', 50)->default('business');
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // 2. Organization Members (Pivot) Table
        Schema::create('organization_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role', 30)->default('member'); // owner, admin, member, viewer
            $table->timestamps();

            $table->unique(['organization_id', 'user_id']);
        });

        // 3. Organization Invitations Table
        Schema::create('organization_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('role', 30)->default('member');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'email']);
        });

        // 4. Link Projects to Organizations
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('organization_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });

        // 5. Organization Pooled Credit Accounts
        Schema::create('organization_credit_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('balance')->default(0);
            $table->unsignedInteger('lifetime_granted')->default(0);
            $table->unsignedInteger('lifetime_consumed')->default(0);
            $table->timestamps();
        });

        // 6. Organization Pooled Credit Transactions
        Schema::create('organization_credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_credit_account_id')
                ->constrained('organization_credit_accounts', 'id', 'org_cred_tx_account_fk')
                ->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 30);
            $table->integer('amount');
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
        Schema::dropIfExists('organization_credit_transactions');
        Schema::dropIfExists('organization_credit_accounts');

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::dropIfExists('organization_invitations');
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};
