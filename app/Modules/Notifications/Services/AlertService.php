<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Services;

use App\Models\User;
use App\Modules\Notifications\Models\Alert;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class AlertService
{
    /**
     * Dispatch an intelligent alert to the project owner.
     * Prevents duplicate alerts if identical unread alert exists within last 24h.
     */
    public function dispatch(
        Project $project,
        string $type,
        string $severity,
        string $title,
        string $message,
        ?array $data = null
    ): Alert {
        // De-duplicate: check if unread identical alert exists
        $existing = Alert::where('project_id', $project->id)
            ->where('type', $type)
            ->where('title', $title)
            ->whereNull('read_at')
            ->where('created_at', '>=', now()->subHours(24))
            ->first();

        if ($existing) {
            $existing->update([
                'message' => $message,
                'data' => $data,
                'updated_at' => now(),
            ]);

            return $existing;
        }

        return Alert::create([
            'project_id' => $project->id,
            'user_id' => $project->user_id,
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'read_at' => null,
        ]);
    }

    /**
     * Mark a single alert as read.
     */
    public function markAsRead(Alert $alert): void
    {
        $alert->markAsRead();
    }

    /**
     * Mark all unread alerts for a user as read.
     */
    public function markAllAsRead(User $user): int
    {
        return Alert::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * Get unread count for user badge.
     */
    public function getUnreadCount(User $user): int
    {
        return Alert::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * Retrieve recent alerts for a user with project details.
     *
     * @return Collection<int, Alert>
     */
    public function getAlertsForUser(User $user, int $limit = 25): Collection
    {
        return Alert::with('project:id,title,status')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
