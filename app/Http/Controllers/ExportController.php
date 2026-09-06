<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Billing\Contracts\EntitlementServiceInterface;
use App\Modules\Blueprint\Contracts\BlueprintServiceInterface;
use App\Modules\Export\Actions\GeneratePdfAction;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(
        protected EntitlementServiceInterface $entitlements,
        protected GeneratePdfAction $generatePdfAction,
        protected ?BlueprintServiceInterface $blueprintService = null
    ) {
        $this->blueprintService = $blueprintService ?? app(BlueprintServiceInterface::class);
    }

    /**
     * Display the exports and generated artifacts catalog.
     */
    public function index(Request $request): \Inertia\Response
    {
        $user = $request->user();

        $projects = $user->projects()
            ->with(['versions', 'repositoryAudit'])
            ->latest()
            ->get();

        return \Inertia\Inertia::render('Exports/Index', [
            'projects' => $projects,
        ]);
    }

    public function generateSignedUrl(Request $request, Project $project): \Illuminate\Http\JsonResponse
    {
        $this->authorize('view', $project);

        $validated = $request->validate([
            'type' => 'required|string|in:package,pdf,growth-plan'
        ]);

        $routeName = match ($validated['type']) {
            'package' => 'export.package.signed',
            'pdf' => 'export.pdf.signed',
            'growth-plan' => 'export.growth-plan.signed',
        };

        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            $routeName,
            now()->addMinutes(30),
            ['project' => $project->id]
        );

        return response()->json([
            'url' => $signedUrl,
            'expires_at' => now()->addMinutes(30)->toIso8601String(),
        ]);
    }

    public function downloadPackage(Request $request, Project $project): StreamedResponse
    {
        if (!$request->hasValidSignature()) {
            $this->authorize('view', $project);
        }

        $user = $request->user() ?? $project->user;

        if (!$this->entitlements->can($user, 'export.package')) {
            abort(402, 'Upgraded plan required for AI Development Package export.');
        }

        $package = $this->blueprintService->generatePackage($request->user(), $project);
        $tempZip = $this->blueprintService->assembleZip($package);

        $filename = 'forge-' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project->title)) . '-package.zip';

        return response()->streamDownload(function () use ($tempZip) {
            readfile($tempZip);
            @unlink($tempZip);
        }, $filename, [
            'Content-Type' => 'application/zip',
        ]);
    }

    public function downloadPdf(Request $request, Project $project): StreamedResponse
    {
        if (!$request->hasValidSignature()) {
            $this->authorize('view', $project);
        }

        $user = $request->user() ?? $project->user;
        $cleanPdf = $this->entitlements->can($user, 'export.pdf.clean');
        $pdf = $this->generatePdfAction->execute($project, $cleanPdf);

        $filename = 'forge-' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project->title)) . '-blueprint.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    public function downloadGrowthPlanPdf(
        Request $request,
        Project $project,
        \App\Modules\Export\Actions\GenerateGrowthPlanPdfAction $generateAction
    ): StreamedResponse {
        if (!$request->hasValidSignature()) {
            $this->authorize('view', $project);
        }

        $user = $request->user() ?? $project->user;
        $cleanPdf = $this->entitlements->can($user, 'export.pdf.clean');
        $pdf = $generateAction->execute($project, $cleanPdf);

        $filename = 'forge-' . strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $project->title)) . '-growth-plan.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
