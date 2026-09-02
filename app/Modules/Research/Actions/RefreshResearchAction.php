<?php

declare(strict_types=1);

namespace App\Modules\Research\Actions;

use App\Models\User;
use App\Modules\Credits\Contracts\CreditServiceInterface;
use App\Modules\Discovery\Services\CompetitorAnalysisService;
use App\Modules\Discovery\Services\DiscoveryService;
use App\Modules\Evidence\Services\EvidenceService;
use App\Modules\Projects\Models\Project;
use App\Modules\Research\Models\ResearchSession;
use App\Modules\Research\Services\ResearchEngine;
use Exception;

class RefreshResearchAction
{
    public function __construct(
        protected CreditServiceInterface $credits,
        protected ResearchEngine $researchEngine,
        protected EvidenceService $evidenceService,
        protected CompetitorAnalysisService $competitorAnalysisService,
        protected DiscoveryService $discoveryService
    ) {}

    /**
     * Re-run an updated research sweep, generating a new session without erasing historical data.
     */
    public function execute(User $user, Project $project): ResearchSession
    {
        $creditCost = 15;
        $reservation = $this->credits->reserve(
            user: $user,
            amount: $creditCost,
            referenceType: 'research.refresh',
            projectId: $project->id
        );

        try {
            // 1. Conduct fresh research sweep
            $researchResult = $this->researchEngine->conductResearch($project, 'market');

            // 2. Register fresh evidence items
            $sources = $researchResult->session->sources()->get()->all();
            $evidenceList = $this->evidenceService->registerEvidenceFromSources($project, $sources);

            // 3. Update competitive landscape
            $this->competitorAnalysisService->analyzeCompetitors($project, $sources);

            // 4. Update discovery verdict
            $this->discoveryService->evaluateDiscovery($project, $evidenceList);

            // 5. Confirm credit consumption
            $this->credits->confirm($reservation);

            return $researchResult->session;

        } catch (Exception $e) {
            $this->credits->release($reservation, "Research refresh failed: {$e->getMessage()}");
            throw $e;
        }
    }
}
