<?php

declare(strict_types=1);

namespace Tests\Feature\Organizations;

use App\Models\User;
use App\Modules\Credits\Actions\ReserveCreditsAction;
use App\Modules\Credits\Services\CreditService;
use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Services\OrganizationService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_creation_provisions_members_and_pooled_credits(): void
    {
        $owner = User::factory()->create();
        $orgService = app(OrganizationService::class);

        $org = $orgService->createOrganization($owner, 'Apex AI Systems', 'enterprise');

        $this->assertInstanceOf(Organization::class, $org);
        $this->assertEquals('Apex AI Systems', $org->name);
        $this->assertEquals('apex-ai-systems', $org->slug);
        $this->assertTrue($org->hasMember($owner));
        $this->assertEquals('owner', $org->getRole($owner));

        // Pooled credit account must have 100 starter team credits
        $this->assertEquals(100, $orgService->getBalance($org));
    }

    public function test_invitation_generation_and_acceptance_flow(): void
    {
        $owner = User::factory()->create();
        $orgService = app(OrganizationService::class);
        $org = $orgService->createOrganization($owner, 'Nova Corp');

        // Owner invites a new engineer as member
        $invitee = User::factory()->create(['email' => 'engineer@nova.example.com']);
        $invitation = $orgService->inviteMember($org, $owner, 'engineer@nova.example.com', 'member');

        $this->assertNotNull($invitation->token);
        $this->assertFalse($invitation->isExpired());
        $this->assertFalse($invitation->isAccepted());

        // Invitee accepts invitation
        $joinedOrg = $orgService->acceptInvitation($invitation->token, $invitee);

        $this->assertEquals($org->id, $joinedOrg->id);
        $this->assertTrue($org->fresh()->hasMember($invitee));
        $this->assertEquals('member', $org->fresh()->getRole($invitee));
        $this->assertTrue($invitation->fresh()->isAccepted());
    }

    public function test_organization_shared_credit_pooling_on_team_projects(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $orgService = app(OrganizationService::class);
        $creditService = app(CreditService::class);

        // Grant member 20 personal credits
        $creditService->grant($member, 20, 'personal_allowance');

        // Create organization (receives 100 pooled credits)
        $org = $orgService->createOrganization($owner, 'Synergy Ventures');
        $org->members()->attach($member->id, ['role' => 'member']);

        // Create an organization project
        $project = Project::create([
            'user_id' => $owner->id,
            'organization_id' => $org->id,
            'title' => 'Synergy Enterprise SaaS',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        // Member reserves 30 credits for an operation on the organization's project
        $reservation = app(ReserveCreditsAction::class)->execute(
            user: $member,
            amount: 30,
            referenceType: 'ai.research',
            referenceId: 'res-123',
            projectId: $project->id
        );

        // Organization pooled credits must be deducted (100 - 30 = 70)
        $this->assertEquals(70, $orgService->getBalance($org));

        // Member's personal credits must remain completely untouched (20 credits)
        $this->assertEquals(20, $creditService->getBalance($member));
    }

    public function test_project_policy_role_based_permissions(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();
        $viewer = User::factory()->create();
        $outsider = User::factory()->create();

        $org = app(OrganizationService::class)->createOrganization($owner, 'Secure Corp');
        $org->members()->attach($admin->id, ['role' => 'admin']);
        $org->members()->attach($member->id, ['role' => 'member']);
        $org->members()->attach($viewer->id, ['role' => 'viewer']);

        $project = Project::create([
            'user_id' => $owner->id,
            'organization_id' => $org->id,
            'title' => 'Confidential Roadmap',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        // 1. Viewer can view, but cannot update or delete
        $this->assertTrue($viewer->can('view', $project));
        $this->assertFalse($viewer->can('update', $project));
        $this->assertFalse($viewer->can('delete', $project));

        // 2. Member can view and update, but cannot delete
        $this->assertTrue($member->can('view', $project));
        $this->assertTrue($member->can('update', $project));
        $this->assertFalse($member->can('delete', $project));

        // 3. Admin can view, update, and delete
        $this->assertTrue($admin->can('view', $project));
        $this->assertTrue($admin->can('update', $project));
        $this->assertTrue($admin->can('delete', $project));

        // 4. Outsider has zero access
        $this->assertFalse($outsider->can('view', $project));
        $this->assertFalse($outsider->can('update', $project));
        $this->assertFalse($outsider->can('delete', $project));
    }

    public function test_organization_http_endpoints(): void
    {
        $owner = User::factory()->create();

        // 1. Create org via POST
        $createRes = $this->actingAs($owner)->postJson(route('organizations.store'), [
            'name' => 'Hyperion Cloud',
        ]);
        $createRes->assertCreated()
            ->assertJsonPath('success', true);

        $orgId = $createRes->json('organization.id');
        $org = Organization::find($orgId);

        // 2. Get org details via GET
        $showRes = $this->actingAs($owner)->getJson(route('organizations.show', $org));
        $showRes->assertOk()
            ->assertJsonPath('role', 'owner')
            ->assertJsonPath('pooled_balance', 100);

        // 3. Invite member via POST
        $inviteRes = $this->actingAs($owner)->postJson(route('organizations.invite', $org), [
            'email' => 'colleague@hyperion.com',
            'role' => 'admin',
        ]);
        $inviteRes->assertOk()
            ->assertJsonPath('success', true);

        $token = $inviteRes->json('invitation.token');

        // 4. Colleague accepts via POST
        $colleague = User::factory()->create(['email' => 'colleague@hyperion.com']);
        $acceptRes = $this->actingAs($colleague)->postJson(route('organizations.invitations.accept', $token));
        $acceptRes->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue($org->fresh()->hasMember($colleague));
    }
}
