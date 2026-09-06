<?php

declare(strict_types=1);

namespace Tests\Feature\Projects;

use App\Models\User;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/projects', [
            'goal' => 'I want to build an AI platform for medical clinics',
            'mode' => 'page_by_page',
        ]);

        $project = Project::where('user_id', $user->id)->first();

        $this->assertNotNull($project);
        $this->assertEquals($user->id, $project->user_id);
        $this->assertEquals(ProjectType::NEW_PRODUCT, $project->classification);
        $this->assertEquals(ProjectStatus::ACTIVE, $project->status);
        $this->assertNotNull($project->workflow);
        $this->assertGreaterThan(0, $project->workflow->stages()->count());

        $response->assertRedirect(route('projects.show', $project));
    }

    public function test_user_cannot_view_another_users_project(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $project = Project::create([
            'user_id' => $owner->id,
            'title' => 'Private Project',
            'description' => 'Owner only',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->actingAs($stranger)->get("/projects/{$project->id}");

        $response->assertForbidden();
    }

    public function test_user_can_create_project_with_website_url_triggering_audit(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/projects', [
            'goal' => 'Improve user conversion and audit site',
            'website_url' => 'https://example-saas.com',
            'mode' => 'page_by_page',
        ]);

        $project = Project::where('user_id', $user->id)->first();

        $this->assertNotNull($project);
        $this->assertNotNull($project->websiteAnalysis);
        $this->assertEquals('https://example-saas.com', $project->websiteAnalysis->url);
        $response->assertRedirect(route('projects.show', $project));
    }
}
