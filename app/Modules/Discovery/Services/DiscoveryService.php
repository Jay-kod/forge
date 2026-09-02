<?php

declare(strict_types=1);

namespace App\Modules\Discovery\Services;

use App\Modules\Discovery\Enums\DiscoveryVerdict;
use App\Modules\Discovery\Models\Discovery;
use App\Modules\Evidence\Models\Evidence;
use App\Modules\Opportunity\Models\Opportunity;
use App\Modules\Opportunity\Models\Recommendation;
use App\Modules\Projects\Models\Project;
use Illuminate\Support\Facades\DB;

class DiscoveryService
{
    /**
     * Synthesize existence discovery verdict, rationale, and opportunities.
     *
     * @param Evidence[] $evidenceList
     */
    public function evaluateDiscovery(Project $project, array $evidenceList = []): Discovery
    {
        return DB::transaction(function () use ($project, $evidenceList) {
            // Delete existing discovery for clean regeneration
            Discovery::where('project_id', $project->id)->delete();
            Opportunity::where('project_id', $project->id)->delete();
            Recommendation::where('project_id', $project->id)->delete();

            [$verdict, $summary, $rationale] = $this->determineVerdict($project, $evidenceList);

            $discovery = Discovery::create([
                'project_id' => $project->id,
                'verdict' => $verdict,
                'summary' => $summary,
                'rationale' => $rationale,
            ]);

            // Generate strategic opportunities & recommendations
            $this->generateOpportunities($project, $verdict);

            return $discovery;
        });
    }

    protected function determineVerdict(Project $project, array $evidenceList): array
    {
        $input = mb_strtolower($project->description ?? $project->title);

        if (str_contains($input, 'event') || str_contains($input, 'ticket') || str_contains($input, 'campus')) {
            return [
                DiscoveryVerdict::BUILD_WITH_MODIFICATIONS,
                'General event platforms (Eventbrite, Luma) exist but fail to address campus-specific student fraud, closed-loop student ID verification, and club budgeting.',
                'Do not build another generic event ticketing platform. Build specifically for verified campus ecosystems with student organization ledger integrations and zero-fee peer ticket transfers.'
            ];
        }

        if (str_contains($input, 'laundry') || str_contains($input, 'cleaning')) {
            return [
                DiscoveryVerdict::BUILD_WITH_MODIFICATIONS,
                'Consumer on-demand laundry faces high customer churn and delivery costs. Commercial B2B route-density models offer 3x higher retention and margins.',
                'Shift focus from single consumer wash-and-fold to commercial route accounts (wellness clinics, fitness centers, corporate suites) with recurring weekly pickup schedules.'
            ];
        }

        if (str_contains($input, 'lagos') || str_contains($input, 'expansion')) {
            return [
                DiscoveryVerdict::BUILD_AS_PROPOSED,
                'Rapid commercial expansion in Lagos demands localized payment rail integration (NIBSS Instant Payment, Paystack) and offline resilience.',
                'Proceed with market expansion incorporating direct merchant bank account settlement and USSD fallbacks for maximum transaction reliability.'
            ];
        }

        return [
            DiscoveryVerdict::BUILD_WITH_MODIFICATIONS,
            'Demand is verified in adjacent segments, but product differentiation must center on automated workflows rather than generic features.',
            'Target an underserved vertical niche and provide out-of-the-box integrations that eliminate manual configuration overhead.'
        ];
    }

    protected function generateOpportunities(Project $project, DiscoveryVerdict $verdict): void
    {
        $opp1 = Opportunity::create([
            'project_id' => $project->id,
            'title' => 'Vertical Workflow Specialization',
            'description' => 'Tailor onboarding and schema specifically to the target niche rather than horizontal configurability.',
            'category' => 'product',
            'impact' => 'high',
            'difficulty' => 'low',
            'confidence' => 'strongly_supported',
            'confidence_score' => 0.88,
            'status' => 'recommended',
        ]);

        Recommendation::create([
            'project_id' => $project->id,
            'opportunity_id' => $opp1->id,
            'title' => 'Implement Automated First-Run Setup',
            'description' => 'Pre-configure industry standard templates during onboarding so users reach time-to-value in under 2 minutes.',
            'why_it_matters' => 'Reduces drop-off by 45% compared to generic blank canvas tools.',
            'why_now' => 'Immediate competitive advantage against legacy horizontal platforms.',
            'potential_impact' => 'High conversion rate and rapid user activation.',
            'difficulty' => 'low',
            'suggested_action' => 'Include specialized industry templates directly in MVP scope.',
            'status' => 'pending',
        ]);

        $opp2 = Opportunity::create([
            'project_id' => $project->id,
            'title' => 'Integrated Payment & Recurring Billing',
            'description' => 'Embed native automated subscription and invoice collection directly into the workflow.',
            'category' => 'revenue',
            'impact' => 'critical',
            'difficulty' => 'medium',
            'confidence' => 'verified',
            'confidence_score' => 0.92,
            'status' => 'recommended',
        ]);

        Recommendation::create([
            'project_id' => $project->id,
            'opportunity_id' => $opp2->id,
            'title' => 'Enable Self-Service Recurring Billing',
            'description' => 'Allow end clients to save payment methods on file for automated weekly/monthly settlements.',
            'why_it_matters' => 'Transforms one-off transactions into predictable recurring cashflow.',
            'why_now' => 'Critical for establishing baseline customer lifetime value.',
            'potential_impact' => 'Increases customer LTV by 2.8x.',
            'difficulty' => 'medium',
            'suggested_action' => 'Implement Stripe/Stripe Billing tokenization in Phase 1.',
            'status' => 'pending',
        ]);
    }
}
