<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Opportunity\Models\Opportunity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OpportunityController extends Controller
{
    /**
     * Display cross-project opportunities.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Opportunity::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->with(['project:id,title,classification,status', 'recommendations']);

        // Optional filtering by category
        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        // Optional filtering by impact
        if ($impact = $request->input('impact')) {
            $query->where('impact', $impact);
        }

        // Optional filtering by difficulty
        if ($difficulty = $request->input('difficulty')) {
            $query->where('difficulty', $difficulty);
        }

        // Search title/description
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $opportunities = $query->latest()->get();

        // Categorized counts for filter tabs
        $categoriesCount = Opportunity::whereHas('project', fn ($q) => $q->where('user_id', $user->id))
            ->selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        return Inertia::render('Opportunities/Index', [
            'opportunities' => $opportunities,
            'categoriesCount' => $categoriesCount,
            'filters' => [
                'category' => $request->input('category'),
                'impact' => $request->input('impact'),
                'difficulty' => $request->input('difficulty'),
                'search' => $request->input('search'),
            ],
        ]);
    }
}
