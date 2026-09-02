<?php

declare(strict_types=1);

namespace App\Modules\Export\Actions;

use App\Modules\Projects\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;

class GenerateGrowthPlanPdfAction
{
    /**
     * Generate printable Growth Plan & Strategic Blueprint PDF for a project.
     */
    public function execute(Project $project, bool $cleanPdf = false): DomPdfWrapper
    {
        $project->loadMissing([
            'context',
            'documents',
            'opportunities.recommendations',
            'researchSessions.sources',
        ]);

        /** @var DomPdfWrapper $pdf */
        $pdf = Pdf::loadView('pdf.growth_plan', [
            'project' => $project,
            'watermark' => !$cleanPdf,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }
}
