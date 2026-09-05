<?php

declare(strict_types=1);

namespace App\Modules\Strategy\Services;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use App\Modules\Strategy\Actions\ChallengeAssumptionsAction;
use App\Modules\Strategy\Actions\GenerateStrategyAction;
use App\Modules\Strategy\Contracts\StrategyServiceInterface;
use App\Modules\Strategy\DTOs\ChallengeResult;
use App\Modules\Strategy\DTOs\StrategyResult;

class StrategyEngine implements StrategyServiceInterface
{
    public function __construct(
        protected ChallengeAssumptionsAction $challengeAction,
        protected GenerateStrategyAction $generateStrategyAction,
        protected CreativeStrategyService $creativeService
    ) {}

    public function challengeAssumptions(User $user, Project $project): ChallengeResult
    {
        return $this->challengeAction->execute($user, $project);
    }

    public function generateStrategy(User $user, Project $project): StrategyResult
    {
        return $this->generateStrategyAction->execute($user, $project);
    }

    public function getCreativeAngles(User $user, Project $project): array
    {
        return $this->creativeService->exploreAngles($user, $project);
    }
}
