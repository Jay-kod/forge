<?php

declare(strict_types=1);

namespace App\Modules\Blueprint\Actions;

use App\Models\User;
use App\Modules\Blueprint\Contracts\BlueprintServiceInterface;
use App\Modules\Blueprint\DTOs\DevPackage;
use App\Modules\Projects\Models\Project;

class GenerateDevPackageAction
{
    public function __construct(
        protected BlueprintServiceInterface $blueprintService
    ) {}

    public function execute(User $user, Project $project): DevPackage
    {
        return $this->blueprintService->generatePackage($user, $project);
    }
}
