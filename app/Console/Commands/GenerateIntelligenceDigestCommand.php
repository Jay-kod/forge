<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\ContinuousIntelligence\Services\IntelligenceDigestService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Illuminate\Console\Command;

class GenerateIntelligenceDigestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forge:generate-digest {--project= : Specific project ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate weekly executive intelligence digests for active projects';

    public function handle(IntelligenceDigestService $digestService): int
    {
        $projectId = $this->option('project');

        $query = Project::where('status', ProjectStatus::ACTIVE);
        if ($projectId) {
            $query->where('id', (int) $projectId);
        }

        $projects = $query->get();

        if ($projects->isEmpty()) {
            $this->info('No active projects found for digest generation.');
            return self::SUCCESS;
        }

        $this->info("Generating intelligence digests for " . $projects->count() . " active project(s)...");

        foreach ($projects as $project) {
            $doc = $digestService->generateForProject($project);
            $this->line(" - Generated Digest [#{$doc->id}]: {$doc->title} for Project [#{$project->id}] {$project->title}");
        }

        $this->info('Intelligence digest generation complete.');

        return self::SUCCESS;
    }
}
