<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Evidence\Models\Evidence;
use App\Modules\Research\Models\ResearchSource;
use App\Modules\Research\Models\WebsiteAnalysis;
use App\Modules\Discovery\Models\Competitor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ResearchCatalogController extends Controller
{
    /**
     * Display cross-project research, evidence, verified sources, and audits.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // 1. Traceable Research Sources across all projects owned by user
        $sources = ResearchSource::whereHas('evidence.project', fn ($q) => $q->where('user_id', $user->id))
            ->with(['evidence.project:id,title'])
            ->latest()
            ->take(50)
            ->get();

        // 2. Synthesized Evidence Items
        $evidence = Evidence::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->with(['project:id,title', 'sources'])
            ->latest()
            ->take(20)
            ->get();

        // 3. Website Analyses
        $websiteAnalyses = WebsiteAnalysis::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->with(['project:id,title'])
            ->latest()
            ->get();

        // 4. Competitor Profiles
        $competitors = Competitor::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->with(['project:id,title'])
            ->latest()
            ->get();

        return Inertia::render('Research/Index', [
            'sources' => $sources,
            'evidence' => $evidence,
            'websiteAnalyses' => $websiteAnalyses,
            'competitors' => $competitors,
        ]);
    }
}
