<?php

declare(strict_types=1);

namespace Tests\Feature\GitHub;

use App\Models\User;
use App\Modules\GitHub\Models\GitHubConnection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GitHubAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_initiate_github_connect(): void
    {
        $response = $this->get(route('github.connect'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_initiate_github_oauth_redirect(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('github.connect'));

        $response->assertStatus(302);
        $this->assertStringContainsString('github.com/login/oauth/authorize', $response->headers->get('Location'));
        $this->assertNotNull(session('github_oauth_state'));
    }

    public function test_user_can_complete_oauth_callback_and_tokens_are_encrypted(): void
    {
        $user = User::factory()->create();
        $state = 'test_random_state_string_12345';

        $response = $this->actingAs($user)
            ->withSession(['github_oauth_state' => $state])
            ->get(route('github.callback', [
                'state' => $state,
                'code' => 'valid_auth_code',
            ]));

        $response->assertRedirect();

        $connection = GitHubConnection::where('user_id', $user->id)->first();
        $this->assertNotNull($connection);
        $this->assertEquals('forge-founder', $connection->github_username);

        // Verify token is encrypted at rest in raw database table
        $rawRecord = DB::table('github_connections')->where('user_id', $user->id)->first();
        $this->assertNotNull($rawRecord);
        $this->assertNotEquals($connection->access_token, $rawRecord->access_token);
        $this->assertStringStartsWith('eyJpdiI', $rawRecord->access_token); // Laravel Encrypter serialized payload
    }

    public function test_user_can_disconnect_github_account(): void
    {
        $user = User::factory()->create();
        GitHubConnection::create([
            'user_id' => $user->id,
            'github_username' => 'forge-dev',
            'access_token' => 'secret_token_abc',
        ]);

        $this->assertNotNull($user->fresh()->githubConnection);

        $response = $this->actingAs($user)->post(route('github.disconnect'));

        $response->assertRedirect();
        $this->assertNull($user->fresh()->githubConnection);
    }

    public function test_user_can_list_accessible_repositories(): void
    {
        $user = User::factory()->create();
        GitHubConnection::create([
            'user_id' => $user->id,
            'github_username' => 'forge-founder',
            'access_token' => 'secret_token_abc',
        ]);

        $response = $this->actingAs($user)->getJson(route('github.repositories'));

        $response->assertStatus(200);
        $response->assertJson([
            'connected' => true,
            'username' => 'forge-founder',
        ]);
        $this->assertNotEmpty($response->json('repositories'));
    }
}
