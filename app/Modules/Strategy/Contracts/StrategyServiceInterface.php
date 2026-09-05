<?php

declare(strict_types=1);

namespace App\Modules\Strategy\Contracts;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Strategy\DTOs\ChallengeResult;
use App\Modules\Strategy\DTOs\StrategyResult;

interface StrategyServiceInterface
{
    /**
     * Challenge foundational assumptions of a project using evidence and competitor context.
     */
    public function challengeAssumptions(User $user, Project $project): ChallengeResult;

    /**
     * Generate actionable strategic recommendation and execution posture for a project.
     */
    public function generateStrategy(User $user, Project $project): StrategyResult;
}
