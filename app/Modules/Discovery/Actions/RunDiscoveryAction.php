<?php

declare(strict_types=1);

namespace App\Modules\Discovery\Actions;

use App\Models\User;
use App\Modules\Credits\Contracts\CreditServiceInterface;
use App\Modules\Discovery\Models\Discovery;
use App\Modules\Discovery\Services\CompetitorAnalysisService;
use App\Modules\Discovery\Services\DiscoveryService;
use App\Modules\Evidence\Services\EvidenceService;
use App\Modules\Projects\Models\Project;
use App\Modules\Research\Services\ResearchEngine;
use Exception;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RunDiscoveryAction
{
    public function __construct(
        protected CreditServiceInterface $credits,
        protected ResearchEngine $researchEngine,
        protected EvidenceService $evidenceService,
        protected CompetitorAnalysisService $competitorAnalysisService,
        protected DiscoveryService $discoveryService,
    ) {}

    public function execute(User $user, Project $project): Discovery
    {
        // 1. Credit reservation for DEEP research & discovery workload
        $creditCost = 15;
        $reservation = $this->credits->reserve(
            user: $user,
            amount: $creditCost,
            referenceType: 'discovery.full_run',
            projectId: $project->id
        );

        try {
            // 2. Conduct real-world research
            $researchResult = $this->researchEngine->conductResearch($project, 'market');

            // 3. Register evidence from collected sources
            $sources = $project->researchSessions()->latest()->first()?->sources()->get()->all() ?? [];
            $evidenceList = $this->evidenceService->registerEvidenceFromSources($project, $sources);

            // 4. Competitor analysis
            $this->competitorAnalysisService->analyzeCompetitors($project, $sources);

            // 5. Synthesize strategic discovery verdict & opportunities
            $discovery = $this->discoveryService->evaluateDiscovery($project, $evidenceList);

            // 6. Confirm credits consumption upon successful analysis
            $this->credits->confirm($reservation);

            return $discovery;

        } catch (Exception $e) {
            // 7. Atomic refund if any step fails
            $this->credits->release($reservation, "Discovery engine failed: {$e->getMessage()}");
            throw new RuntimeException("Discovery execution failed. Credits have been refunded. {$e->getMessage()}", 0, $e);
        }
    }
}
