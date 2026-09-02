<?php

declare(strict_types=1);

namespace Tests\Feature\GitHub;

use App\Models\User;
use App\Modules\GitHub\Models\GitHubConnection;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GitHubExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_trigger_repository_scan(): void
    {
        $user = User::factory()->create();
        GitHubConnection::create([
            'user_id' => $user->id,
            'github_username' => 'forge-dev',
            'access_token' => 'dummy_token',
        ]);

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'E-Commerce Platform',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->actingAs($user)->postJson(route('projects.github.scan', $project), [
            'repo_full_name' => 'forge-dev/ecommerce-api',
            'branch' => 'main',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('audit.repo_full_name', 'forge-dev/ecommerce-api');

        $this->assertDatabaseHas('repository_audits', [
            'project_id' => $project->id,
            'repo_full_name' => 'forge-dev/ecommerce-api',
        ]);
    }

    public function test_user_can_export_blueprints_to_isolated_branch(): void
    {
        $user = User::factory()->create();
        GitHubConnection::create([
            'user_id' => $user->id,
            'github_username' => 'forge-dev',
            'access_token' => 'dummy_token',
        ]);

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'E-Commerce Platform',
            'status' => ProjectStatus::ACTIVE,
        ]);

        // Attempt export with 'main' as branch; should safely isolate to forge/blueprint-*
        $response = $this->actingAs($user)->postJson(route('projects.github.export', $project), [
            'repo_full_name' => 'forge-dev/ecommerce-api',
            'branch' => 'main',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('repo', 'forge-dev/ecommerce-api');

        $data = $response->json();
        $this->assertStringStartsWith('forge/blueprint', $data['branch']);
        $this->assertNotEquals('main', $data['branch']);
        $this->assertNotEmpty($data['files_committed']);
        $this->assertContains('FORGE_BLUEPRINT.md', $data['files_committed']);
    }

    public function test_unauthorized_user_cannot_export_project(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'title' => 'Confidential Project',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->actingAs($stranger)->postJson(route('projects.github.export', $project), [
            'repo_full_name' => 'owner/secret-repo',
            'branch' => 'forge/blueprint',
        ]);

        $response->assertForbidden();
    }
}
