<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\ContinuousIntelligence\Services\CompetitorDriftMonitor;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Models\Project;
use Illuminate\Console\Command;

class MonitorCompetitorsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'forge:monitor-competitors {--project= : Specific project ID to scan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan project competitors and connected codebases for strategic drift and new alerts';

    public function handle(CompetitorDriftMonitor $monitor): int
    {
        $projectId = $this->option('project');

        $query = Project::where('status', ProjectStatus::ACTIVE);
        if ($projectId) {
            $query->where('id', (int) $projectId);
        }

        $projects = $query->with('competitors')->get();

        if ($projects->isEmpty()) {
            $this->info('No active projects found for competitor monitoring.');
            return self::SUCCESS;
        }

        $this->info("Scanning " . $projects->count() . " active project(s) for competitive drift...");

        $totalDrifts = 0;
        $totalAlerts = 0;

        foreach ($projects as $project) {
            $result = $monitor->monitorProject($project);
            $totalDrifts += $result['drifts_detected'];
            $totalAlerts += $result['alerts_dispatched'];

            $this->line(" - Project [#{$project->id}] {$project->title}: {$result['analyzed_competitors']} competitors, {$result['drifts_detected']} drifts detected.");
            foreach ($project->competitors as $comp) {
                $this->line("   * Competitor: {$comp->name}");
            }
        }

        $this->info("Monitoring complete. Total drifts detected: {$totalDrifts}, Alerts dispatched: {$totalAlerts}.");

        return self::SUCCESS;
    }
}
