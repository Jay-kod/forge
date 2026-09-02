<?php

declare(strict_types=1);

namespace Tests\Feature\API;

use App\Models\User;
use App\Modules\API\Services\ApiKeyService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Enums\WorkflowMode;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_can_list_and_create_projects(): void
    {
        $user = User::factory()->create();
        $auth = app(ApiKeyService::class)->createKey($user, 'Test API Key');
        $token = $auth['plain_token'];

        // 1. Create project via POST /api/v1/projects
        $createRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/projects', [
                'title' => 'API Generated SaaS',
                'description' => 'Created automatically from CLI',
                'classification' => 'new_product',
                'workflow_mode' => 'automatic',
            ]);

        $createRes->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('project.title', 'API Generated SaaS');

        $projectId = $createRes->json('project.id');
        $this->assertDatabaseHas('projects', ['id' => $projectId]);
        $this->assertDatabaseHas('project_contexts', ['project_id' => $projectId]);

        // 2. List projects via GET /api/v1/projects
        $listRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/projects');

        $listRes->assertOk()
            ->assertJsonStructure(['projects' => ['data']]);
        $this->assertCount(1, $listRes->json('projects.data'));

        // 3. Retrieve single project via GET /api/v1/projects/{project}
        $project = Project::find($projectId);
        $showRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/projects/{$project->id}");

        $showRes->assertOk()
            ->assertJsonPath('project.id', $project->id)
            ->assertJsonPath('project.title', 'API Generated SaaS');

        // 4. Retrieve opportunities via GET /api/v1/projects/{project}/opportunities
        $oppRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/projects/{$project->id}/opportunities");

        $oppRes->assertOk()
            ->assertJsonPath('project_id', $project->id)
            ->assertJsonStructure(['project_id', 'competitors_count', 'matrix']);

        // 5. Retrieve graph via GET /api/v1/projects/{project}/graph
        $graphRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson("/api/v1/projects/{$project->id}/graph");

        $graphRes->assertOk()
            ->assertJsonStructure(['nodes', 'edges']);
    }
}
