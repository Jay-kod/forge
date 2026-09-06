<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $currentProject = null;
        $projectParam = $request->route('project');
        if ($projectParam instanceof \App\Modules\Projects\Models\Project) {
            $currentProject = [
                'id' => $projectParam->id,
                'title' => $projectParam->title,
                'classification' => is_object($projectParam->classification) ? $projectParam->classification->value : (string) $projectParam->classification,
                'status' => is_object($projectParam->status) ? $projectParam->status->value : (string) $projectParam->status,
            ];
        } elseif (is_numeric($projectParam) && $user) {
            $p = \App\Modules\Projects\Models\Project::where('user_id', $user->id)->find($projectParam);
            if ($p) {
                $currentProject = [
                    'id' => $p->id,
                    'title' => $p->title,
                    'classification' => is_object($p->classification) ? $p->classification->value : (string) $p->classification,
                    'status' => is_object($p->status) ? $p->status->value : (string) $p->status,
                ];
            }
        }

        $entitlements = $user ? app(\App\Modules\Billing\Contracts\EntitlementServiceInterface::class) : null;
        $capabilities = $user && $entitlements ? [
            'can_create_project' => $entitlements->can($user, 'project.create'),
            'can_export_package' => $entitlements->can($user, 'export.package'),
            'can_export_growth' => $entitlements->can($user, 'export.growth_plan'),
            'can_automatic_workflow' => $entitlements->can($user, 'workflow.automatic'),
            'can_connect_github' => true,
        ] : [];

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url,
                    'role' => $user->role ?? 'user',
                    'technical_level' => $user->technical_level,
                    'referral_code' => $user->referral_code,
                ] : null,
            ],
            'credits' => [
                'balance' => $user && method_exists($user, 'creditAccount') && $user->creditAccount 
                    ? $user->creditAccount->balance 
                    : ($user ? 25 : 0),
            ],
            'plan' => [
                'name' => $user?->subscription?->plan?->name ?? 'Free Tier',
                'slug' => $user?->subscription?->plan?->slug ?? 'free',
                'is_pro' => in_array($user?->subscription?->plan?->slug ?? '', ['pro', 'growth', 'enterprise'], true),
                'status' => $user?->subscription?->status ?? 'active',
            ],
            'capabilities' => $capabilities,
            'current_project' => $currentProject,
            'has_github' => $user ? $user->githubConnection()->exists() : false,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'warning' => fn () => $request->session()->get('warning'),
            ],
            'appName' => config('app.name', 'FORGE'),
        ];
    }
}
