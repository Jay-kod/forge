<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Contracts;

use App\Models\User;
use App\Modules\Blueprint\DTOs\DevPackage;
use App\Modules\Projects\Models\Project;

interface BlueprintServiceInterface
{
    /**
     * Synthesize complete AI development package for a project.
     */
    public function generatePackage(User $user, Project $project): DevPackage;

    /**
     * Generate master copy-to-clipboard prompt for AI coding tools.
     */
    public function generateMasterPrompt(Project $project): string;

    /**
     * Assemble development package into a zip archive path.
     */
    public function assembleZip(DevPackage $package): string;
}
