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
        if ($user->id === $project->user_id || $user->isAdmin()) {
            return true;
        }

        if ($project->organization_id && $project->organization) {
            return $project->organization->hasMember($user);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->id === $project->user_id || $user->isAdmin()) {
            return true;
        }

        if ($project->organization_id && $project->organization) {
            return $project->organization->hasRole($user, ['owner', 'admin', 'member']);
        }

        return false;
    }

    public function delete(User $user, Project $project): bool
    {
        if ($user->id === $project->user_id || $user->isAdmin()) {
            return true;
        }

        if ($project->organization_id && $project->organization) {
            return $project->organization->hasRole($user, ['owner', 'admin']);
        }

        return false;
    }
}
