<?php

declare(strict_types=1);

namespace Tests\Feature\Export;

use App\Models\User;
use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Enums\WorkflowMode;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_owner_can_download_project_pdf(): void
    {
        $user = User::factory()->create();

        $project = app(CreateProjectAction::class)->execute(
            user: $user,
            userInput: 'Building an event platform for students',
            title: 'Campus Ticketing App',
            mode: WorkflowMode::PAGE_BY_PAGE
        );

        $response = $this->actingAs($user)->get(route('export.pdf', $project));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('campus-ticketing-app', (string) $response->headers->get('content-disposition'));
    }

    public function test_unauthorized_user_cannot_download_project_pdf(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $project = app(CreateProjectAction::class)->execute(
            user: $owner,
            userInput: 'Private startup plan',
            title: 'Confidential Workspace',
            mode: WorkflowMode::PAGE_BY_PAGE
        );

        $response = $this->actingAs($stranger)->get(route('export.pdf', $project));

        $response->assertForbidden();
    }
}
