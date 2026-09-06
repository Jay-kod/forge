<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\GitHub\Models\RepositoryAudit;
use App\Modules\Opportunity\Models\Opportunity;
use App\Modules\Opportunity\Models\Recommendation;
use App\Modules\Research\Models\WebsiteAnalysis;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GrowthController extends Controller
{
    /**
     * Display the proactive Growth Center, adapted to the user's role and technical profile.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isDeveloper = in_array($user->technical_level?->value ?? '', ['developer', 'senior_developer'], true);

        // 1. Growth-focused recommendations
        $recommendations = Recommendation::whereHas('opportunity.project', fn ($q) => $q->where('user_id', $user->id))
            ->with(['opportunity.project:id,title,classification'])
            ->latest()
            ->take(15)
            ->get();

        // 2. Opportunities categorized for business growth & technical expansion
        $businessOpportunities = Opportunity::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->whereIn('category', ['growth', 'market', 'revenue', 'expansion', 'business'])
            ->with('project:id,title')
            ->latest()
            ->take(10)
            ->get();

        $technicalOpportunities = Opportunity::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->whereIn('category', ['technical', 'architecture', 'automation', 'performance', 'security'])
            ->with('project:id,title')
            ->latest()
            ->take(10)
            ->get();

        // 3. Technical audits (repositories)
        $repositoryAudits = RepositoryAudit::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->with('project:id,title')
            ->latest()
            ->take(5)
            ->get();

        // 4. Conversion & SEO audits (websites)
        $websiteAudits = WebsiteAnalysis::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->with('project:id,title')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Growth/Index', [
            'isDeveloper' => $isDeveloper,
            'technicalLevel' => $user->technical_level?->value ?? 'non_developer',
            'recommendations' => $recommendations,
            'businessOpportunities' => $businessOpportunities,
            'technicalOpportunities' => $technicalOpportunities,
            'repositoryAudits' => $repositoryAudits,
            'websiteAudits' => $websiteAudits,
        ]);
    }
}
