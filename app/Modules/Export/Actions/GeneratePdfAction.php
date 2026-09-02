<?php

declare(strict_types=1);

namespace App\Modules\Export\Actions;

use App\Modules\Projects\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;

class GeneratePdfAction
{
    /**
     * Generate printable PDF report for a project.
     */
    public function execute(Project $project, bool $cleanPdf = false): DomPdfWrapper
    {
        $project->loadMissing([
            'context',
            'discovery',
            'competitors',
            'evidence.sources',
            'opportunities.recommendations',
        ]);

        /** @var DomPdfWrapper $pdf */
        $pdf = Pdf::loadView('pdf.report', [
            'project' => $project,
            'cleanPdf' => $cleanPdf,
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf;
    }
}
