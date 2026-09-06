<?php

declare(strict_types=1);

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Opportunity\Models\Opportunity;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_all_level_1_navigation_routes(): void
    {
        $routes = [
            'dashboard',
            'discover',
            'opportunities.index',
            'research.index',
            'growth.index',
            'github.index',
            'notifications.index',
            'exports.index',
            'usage.index',
            'settings.index',
            'help.index',
        ];

        foreach ($routes as $route) {
            $response = $this->get(route($route));
            $response->assertRedirect(route('login'));
        }
    }

    public function test_authenticated_user_can_view_dashboard_with_real_metrics(): void
    {
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Healthcare AI Assistant',
            'description' => 'Automated clinical documentation',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
            'workflow_mode' => 'page_by_page',
            'current_stage' => 'understanding',
        ]);

        Opportunity::create([
            'project_id' => $project->id,
            'title' => 'Automate Discharge Summaries',
            'description' => 'High-leverage time saver for physicians',
            'category' => 'automation',
            'impact' => 'critical',
            'difficulty' => 'medium',
            'confidence' => 'verified',
            'confidence_score' => 0.95,
            'status' => 'identified',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('metrics')
            ->where('metrics.totalProjects', 1)
            ->where('metrics.totalOpportunities', 1)
            ->has('recentProjects', 1)
            ->has('opportunities', 1)
        );
    }

    public function test_authenticated_user_can_view_all_level_1_views(): void
    {
        $user = User::factory()->create();

        $routes = [
            'discover' => 'Discover',
            'opportunities.index' => 'Opportunities/Index',
            'research.index' => 'Research/Index',
            'growth.index' => 'Growth/Index',
            'github.index' => 'GitHub/Index',
            'notifications.index' => 'Notifications/Index',
            'exports.index' => 'Exports/Index',
            'usage.index' => 'Usage/Index',
            'settings.index' => 'Settings/Index',
            'help.index' => 'Help/Index',
        ];

        foreach ($routes as $route => $component) {
            $response = $this->actingAs($user)->get(route($route));
            $response->assertOk();
            $response->assertInertia(fn ($page) => $page->component($component));
        }
    }

    public function test_opportunities_query_isolates_user_data(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $projA = Project::create([
            'user_id' => $userA->id,
            'title' => 'Project User A',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
            'workflow_mode' => 'page_by_page',
            'current_stage' => 'understanding',
        ]);

        $projB = Project::create([
            'user_id' => $userB->id,
            'title' => 'Project User B',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
            'workflow_mode' => 'page_by_page',
            'current_stage' => 'understanding',
        ]);

        Opportunity::create([
            'project_id' => $projA->id,
            'title' => 'Opportunity For User A Only',
            'description' => 'Detailed opportunity description for User A',
            'category' => 'product',
            'impact' => 'high',
            'difficulty' => 'low',
            'confidence' => 'verified',
            'status' => 'identified',
        ]);

        Opportunity::create([
            'project_id' => $projB->id,
            'title' => 'Opportunity For User B Only',
            'description' => 'Detailed opportunity description for User B',
            'category' => 'growth',
            'impact' => 'critical',
            'difficulty' => 'high',
            'confidence' => 'verified',
            'status' => 'identified',
        ]);

        // User A must only see User A's opportunities
        $responseA = $this->actingAs($userA)->get(route('opportunities.index'));
        $responseA->assertOk();
        $responseA->assertInertia(fn ($page) => $page
            ->component('Opportunities/Index')
            ->has('opportunities', 1)
            ->where('opportunities.0.title', 'Opportunity For User A Only')
        );

        // User B must only see User B's opportunities
        $responseB = $this->actingAs($userB)->get(route('opportunities.index'));
        $responseB->assertOk();
        $responseB->assertInertia(fn ($page) => $page
            ->component('Opportunities/Index')
            ->has('opportunities', 1)
            ->where('opportunities.0.title', 'Opportunity For User B Only')
        );
    }

    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        $regularUser = User::factory()->create([
            'role' => UserRole::USER,
        ]);

        $response = $this->actingAs($regularUser)->get(route('admin.dashboard'));
        $response->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $adminUser = User::factory()->create([
            'role' => UserRole::ADMIN,
        ]);

        $response = $this->actingAs($adminUser)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('Admin/Dashboard'));
    }

    public function test_discover_submission_creates_project_and_redirects(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('discover.submit'), [
            'prompt' => 'I want to build a platform for logistics dispatch and route optimization in Lagos',
            'mode' => 'page_by_page',
        ]);

        $response->assertSessionHasNoErrors();
        $project = Project::where('user_id', $user->id)->first();

        $this->assertNotNull($project);
        $response->assertRedirect(route('projects.show', $project));
    }
}
