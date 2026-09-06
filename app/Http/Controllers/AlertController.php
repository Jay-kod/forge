<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Notifications\Models\Alert;
use App\Modules\Notifications\Services\AlertService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function __construct(
        protected AlertService $alertService
    ) {}

    /**
     * List user alerts and unread count, or render Notifications page.
     */
    public function index(Request $request): JsonResponse|\Inertia\Response
    {
        $user = $request->user();

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'alerts' => $this->alertService->getAlertsForUser($user),
                'unread_count' => $this->alertService->getUnreadCount($user),
            ]);
        }

        return \Inertia\Inertia::render('Notifications/Index', [
            'alerts' => Alert::where('user_id', $user->id)
                ->with('project:id,title')
                ->latest()
                ->paginate(20),
            'unreadCount' => $this->alertService->getUnreadCount($user),
        ]);
    }

    /**
     * Mark a single alert as read.
     */
    public function markRead(Request $request, Alert $alert): JsonResponse
    {
        if ($alert->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized.');
        }

        $this->alertService->markAsRead($alert);

        return response()->json([
            'success' => true,
            'unread_count' => $this->alertService->getUnreadCount($request->user()),
        ]);
    }

    /**
     * Mark all user alerts as read.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $updated = $this->alertService->markAllAsRead($request->user());

        return response()->json([
            'success' => true,
            'marked_count' => $updated,
            'unread_count' => 0,
        ]);
    }
}
