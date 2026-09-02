<?php

declare(strict_types=1);

namespace App\Modules\Research\Services;

use App\Modules\Projects\Models\Project;
use App\Modules\Research\Contracts\WebSearchProviderInterface;
use App\Modules\Research\DTOs\ResearchQuery;
use App\Modules\Research\DTOs\ResearchResult;
use App\Modules\Research\Models\ResearchSession;
use App\Modules\Research\Models\ResearchSource;
use Illuminate\Support\Facades\DB;

class ResearchEngine
{
    public function __construct(
        protected WebSearchProviderInterface $searchProvider
    ) {}

    /**
     * Conduct real-world research for a project and store traceable sources.
     */
    public function conductResearch(Project $project, string $researchType = 'market'): ResearchResult
    {
        return DB::transaction(function () use ($project, $researchType) {
            // 1. Create Research Session
            $session = ResearchSession::create([
                'project_id' => $project->id,
                'type' => $researchType,
                'status' => 'running',
                'started_at' => now(),
                'credits_consumed' => 15,
            ]);

            // 2. Formulate query
            $searchQueryText = $this->buildSearchQuery($project, $researchType);
            $query = new ResearchQuery(
                query: $searchQueryText,
                type: $researchType,
                maxSources: 6
            );

            // 3. Search and extract raw sources
            $rawSources = $this->searchProvider->search($query);

            // 4. Store traceable sources
            $storedSources = [];
            foreach ($rawSources as $raw) {
                $storedSources[] = ResearchSource::create([
                    'research_session_id' => $session->id,
                    'url' => $raw->url,
                    'title' => $raw->title,
                    'source_type' => $raw->sourceType,
                    'publication_date' => $raw->publicationDate,
                    'retrieved_at' => now(),
                    'content_summary' => $raw->snippet,
                    'reliability_score' => $raw->reliabilityScore,
                ]);
            }

            $session->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            return new ResearchResult(
                session: $session,
                sources: $rawSources,
                findings: [
                    'topic' => $searchQueryText,
                    'sources_count' => count($storedSources),
                    'average_reliability' => count($rawSources) > 0 
                        ? array_sum(array_map(fn ($s) => $s->reliabilityScore, $rawSources)) / count($rawSources)
                        : 0.8,
                ],
                creditsConsumed: 15
            );
        });
    }

    protected function buildSearchQuery(Project $project, string $type): string
    {
        $desc = $project->description ?? $project->title;
        $cleanDesc = trim(preg_replace('/[^\w\s]/', '', $desc));

        return match ($type) {
            'competitor' => "{$cleanDesc} direct competitors pricing alternatives market share",
            'market' => "{$cleanDesc} market size customer demand industry trends",
            'technology' => "{$cleanDesc} architecture stack dependencies api ecosystem",
            default => "{$cleanDesc} market research",
        };
    }
}
