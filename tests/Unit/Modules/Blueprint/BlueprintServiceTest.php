<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Blueprint;

use App\Models\User;
use App\Modules\Blueprint\Actions\GenerateMasterPromptAction;
use App\Modules\Blueprint\Services\BlueprintService;
use App\Modules\Blueprint\Services\PackageAssembler;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use ZipArchive;

class BlueprintServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BlueprintService $blueprintService;
    protected User $user;
    protected Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->project = Project::create([
            'user_id' => $this->user->id,
            'title' => 'PulseFlow Health',
            'description' => 'Telehealth triaging and remote vitals tracking platform',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => \App\Modules\Projects\Enums\ProjectStatus::ACTIVE,
        ]);

        $this->blueprintService = new BlueprintService(
            new GenerateMasterPromptAction(),
            new PackageAssembler()
        );
    }

    public function test_generate_package_creates_all_specification_files(): void
    {
        $package = $this->blueprintService->generatePackage($this->user, $this->project);

        $this->assertEquals('PulseFlow Health', $package->projectName);
        $this->assertNotEmpty($package->masterPrompt);
        $this->assertStringContainsString('PulseFlow Health', $package->masterPrompt);

        $fileMap = $package->toFileMap();

        $expectedFiles = [
            'README.md',
            'AGENTS.md',
            'CLAUDE.md',
            'MASTER-PROMPT.md',
            'docs/01-prd.md',
            'docs/03-architecture.md',
            'docs/04-architecture-essentials.md',
            'docs/05-hard-questions.md',
            'docs/07-testing-strategy.md',
        ];

        foreach ($expectedFiles as $expectedFile) {
            $this->assertArrayHasKey($expectedFile, $fileMap, "Missing expected package file: {$expectedFile}");
            $this->assertNotEmpty($fileMap[$expectedFile]);
        }

        // Verify AGENTS.md content includes core rules
        $this->assertStringContainsString('PulseFlow Health', $fileMap['AGENTS.md']);
        $this->assertStringContainsString('Server-Side Security', $fileMap['AGENTS.md']);
    }

    public function test_package_assembler_creates_valid_zip_archive(): void
    {
        $package = $this->blueprintService->generatePackage($this->user, $this->project);
        $zipPath = $this->blueprintService->assembleZip($package);

        $this->assertFileExists($zipPath);

        $zip = new ZipArchive();
        $openResult = $zip->open($zipPath);
        $this->assertTrue($openResult === true, "Failed to open generated ZIP file");

        $this->assertGreaterThanOrEqual(9, $zip->numFiles);

        // Verify key file contents inside ZIP
        $readme = $zip->getFromName('README.md');
        $this->assertIsString($readme);
        $this->assertStringContainsString('PulseFlow Health', $readme);

        $agents = $zip->getFromName('AGENTS.md');
        $this->assertIsString($agents);
        $this->assertStringContainsString('Agent Operating Instructions', $agents);

        $zip->close();
        @unlink($zipPath);
    }
}
