<?php

declare(strict_types=1);

namespace Tests\Feature\Consent;

use App\Models\User;
use App\Modules\AI\Models\ByokCredential;
use App\Modules\API\Models\ApiKey;
use App\Modules\Credits\Services\CreditService;
use App\Modules\GitHub\Models\GitHubConnection;
use App\Modules\Organizations\Models\AuditLog;
use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_purge_account_with_valid_password(): void
    {
        $password = 'Secret123!';
        $user = User::factory()->create([
            'password' => Hash::make($password),
        ]);

        // 1. Create resources attached to user
        $creditService = app(CreditService::class);
        $creditService->grant($user, 100, description: 'Personal onboarding bonus');

        $project = app(CreateProjectAction::class)->execute($user, 'Project to be purged', 'Purge Test Project');

        ByokCredential::create([
            'user_id' => $user->id,
            'provider' => 'openai',
            'api_key' => 'sk-test-key-12345',
            'label' => 'Test Key',
        ]);

        GitHubConnection::create([
            'user_id' => $user->id,
            'github_user_id' => '123456',
            'github_username' => 'testuser',
            'access_token' => 'gho_token_123',
        ]);

        ApiKey::create([
            'user_id' => $user->id,
            'name' => 'CI Bot',
            'key_hash' => hash('sha256', 'plain_test_token'),
            'prefix' => 'forge_live_test',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'project.created',
            'ip_address' => '192.168.1.100',
        ]);

        $userId = $user->id;

        // 2. Send account purge request
        $response = $this->actingAs($user)->deleteJson('/settings/privacy/account', [
            'password' => $password,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // 3. Verify Right to Be Forgotten
        $this->assertDatabaseMissing('users', ['id' => $userId]);
        $this->assertDatabaseMissing('projects', ['user_id' => $userId]);
        $this->assertDatabaseMissing('byok_credentials', ['user_id' => $userId]);
        $this->assertDatabaseMissing('github_connections', ['user_id' => $userId]);
        $this->assertDatabaseMissing('api_keys', ['user_id' => $userId]);

        // 4. Verify audit log anonymization
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => null,
            'ip_address' => '0.0.0.0',
        ]);
    }

    public function test_deletion_requires_valid_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('CorrectPassword!'),
        ]);

        $response = $this->actingAs($user)->deleteJson('/settings/privacy/account', [
            'password' => 'WrongPassword!',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', 'Invalid confirmation password provided.');

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
