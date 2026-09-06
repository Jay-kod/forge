<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Notifications\Models\Alert;
use App\Modules\Opportunity\Models\Opportunity;
use App\Modules\Opportunity\Models\Recommendation;
use App\Modules\Projects\Models\Project;
use App\Modules\Research\Models\ResearchSource;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the main authenticated user dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 1. User Projects
        $recentProjects = $user->projects()
            ->with(['workflow.stages'])
            ->latest()
            ->take(5)
            ->get();

        $totalProjectsCount = $user->projects()->count();
        $completedProjectsCount = $user->projects()->where('status', 'completed')->count();

        // 2. High-Priority Discovered Opportunities across user projects
        $opportunities = Opportunity::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->with('project')
            ->orderByRaw("FIELD(impact, 'critical', 'high', 'medium', 'low')")
            ->latest()
            ->take(5)
            ->get();

        $totalOpportunitiesCount = Opportunity::whereHas('project', fn ($q) => $q->where('user_id', $user->id))->count();

        // 3. Proactive Recommendations
        $recommendations = Recommendation::whereHas('opportunity.project', fn ($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(4)
            ->get();

        // 4. Traceable Research Evidence Count
        $sourcesCount = ResearchSource::whereHas('evidence.project', fn ($q) => $q->where('user_id', $user->id))->count();

        // 5. Recent In-App Alerts
        $recentAlerts = Alert::where('user_id', $user->id)
            ->unread()
            ->latest()
            ->take(4)
            ->get();

        // 6. Plan & Credit Summary
        $creditBalance = $user->creditAccount ? $user->creditAccount->balance : 25;
        $activeSubscription = $user->subscription?->load('plan');

        return Inertia::render('Dashboard', [
            'recentProjects' => $recentProjects,
            'metrics' => [
                'totalProjects' => $totalProjectsCount,
                'completedProjects' => $completedProjectsCount,
                'totalOpportunities' => $totalOpportunitiesCount,
                'totalSources' => $sourcesCount,
                'creditBalance' => $creditBalance,
                'planName' => $activeSubscription?->plan?->name ?? 'Free Tier',
            ],
            'opportunities' => $opportunities,
            'recommendations' => $recommendations,
            'recentAlerts' => $recentAlerts,
        ]);
    }
}
