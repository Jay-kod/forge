<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Enums\WorkflowMode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscoverController extends Controller
{
    public function __construct(
        protected CreateProjectAction $createProjectAction
    ) {}

    /**
     * Show the Discover intelligence entrypoint.
     */
    public function index(): Response
    {
        return Inertia::render('Discover');
    }

    /**
     * Process natural-language intent and initiate discovery.
     */
    public function submit(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'prompt' => ['required', 'string', 'min:5', 'max:5000'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'mode' => ['nullable', 'string', 'in:page_by_page,automatic'],
        ]);

        $mode = ($validated['mode'] ?? 'page_by_page') === 'automatic'
            ? WorkflowMode::AUTOMATIC
            : WorkflowMode::PAGE_BY_PAGE;

        try {
            $project = $this->createProjectAction->execute(
                user: $request->user(),
                userInput: $validated['prompt'],
                title: null,
                mode: $mode,
                websiteUrl: $validated['website_url'] ?? null
            );

            return redirect()->route('projects.show', $project->id)
                ->with('success', "Discovery launched for '{$project->title}'. Context initialized.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
