<?php

declare(strict_types=1);

namespace App\Modules\Projects\Policies;

use App\Models\User;
use App\Modules\Projects\Models\Project;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        return $user->id === $project->user_id || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        return $user->id === $project->user_id || $user->isAdmin();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->id === $project->user_id || $user->isAdmin();
    }
}
