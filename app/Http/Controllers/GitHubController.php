<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\GitHub\Models\GitHubConnection;
use App\Modules\GitHub\Services\GitHubClientService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GitHubController extends Controller
{
    public function __construct(
        protected GitHubClientService $github
    ) {}

    /**
     * Redirect user to GitHub OAuth with repo scopes.
     */
    public function connect(Request $request): RedirectResponse
    {
        $state = Str::random(40);
        $request->session()->put('github_oauth_state', $state);

        return redirect()->away($this->github->getOAuthUrl($state));
    }

    /**
     * Handle OAuth callback from GitHub.
     */
    public function callback(Request $request): RedirectResponse
    {
        $state = $request->session()->pull('github_oauth_state');
        $inputState = $request->query('state');

        if (!$state || $state !== $inputState) {
            return redirect()->route('projects.index')
                ->with('error', 'Invalid GitHub OAuth state. Please try connecting again.');
        }

        $code = (string) $request->query('code');
        if (empty($code)) {
            return redirect()->route('projects.index')
                ->with('error', 'No authorization code provided by GitHub.');
        }

        $tokenData = $this->github->exchangeCodeForToken($code);
        $accessToken = $tokenData['access_token'] ?? null;

        if (!$accessToken) {
            return redirect()->route('projects.index')
                ->with('error', 'Failed to retrieve access token from GitHub.');
        }

        $profile = $this->github->getUserProfile($accessToken);

        GitHubConnection::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'github_user_id' => (string) ($profile['id'] ?? ''),
                'github_username' => $profile['login'] ?? null,
                'avatar_url' => $profile['avatar_url'] ?? null,
                'access_token' => $accessToken,
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'scope' => $tokenData['scope'] ?? 'repo,read:user',
                'token_type' => $tokenData['token_type'] ?? 'bearer',
                'expires_at' => isset($tokenData['expires_in']) ? now()->addSeconds((int) $tokenData['expires_in']) : null,
            ]
        );

        return redirect()->back()
            ->with('success', 'Successfully connected your GitHub repository account!');
    }

    /**
     * Disconnect and revoke GitHub integration.
     */
    public function disconnect(Request $request): RedirectResponse
    {
        $request->user()->githubConnection()?->delete();

        return redirect()->back()
            ->with('success', 'GitHub account disconnected successfully.');
    }

    /**
     * Fetch user's accessible repositories.
     */
    public function repositories(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->githubConnection) {
            return response()->json([
                'connected' => false,
                'repositories' => [],
            ]);
        }

        try {
            $repos = $this->github->getUserRepositories($user);

            return response()->json([
                'connected' => true,
                'username' => $user->githubConnection->github_username,
                'repositories' => $repos,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'connected' => true,
                'error' => $e->getMessage(),
                'repositories' => [],
            ], 500);
        }
    }

    /**
     * Scan and audit a selected repository for a project.
     */
    public function scan(
        Request $request,
        \App\Modules\Projects\Models\Project $project,
        \App\Modules\GitHub\Services\RepositoryScannerService $scanner,
        \App\Modules\GitHub\Services\TechnicalDebtAuditor $auditor,
        \App\Modules\GitHub\Services\CodeOpportunityGenerator $opportunityGenerator
    ): JsonResponse {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'repo_full_name' => ['required', 'string', 'regex:/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/'],
            'branch' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $branch = $validated['branch'] ?? 'main';
            $audit = $scanner->scan($project, $validated['repo_full_name'], $branch);
            $auditor->audit($audit);
            $opportunityGenerator->generate($project, $audit);

            return response()->json([
                'success' => true,
                'audit' => $audit->fresh(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Export blueprint documentation and roadmap directly to an isolated branch on GitHub.
     */
    public function export(
        Request $request,
        \App\Modules\Projects\Models\Project $project,
        \App\Modules\GitHub\Services\GitHubExportService $exportService
    ): JsonResponse {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'repo_full_name' => ['required', 'string', 'regex:/^[A-Za-z0-9_.-]+\/[A-Za-z0-9_.-]+$/'],
            'branch' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            $result = $exportService->export(
                $request->user(),
                $project,
                $validated['repo_full_name'],
                $validated['branch'] ?? null
            );

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
