<?php

declare(strict_types=1);

namespace App\Modules\Consent\Services;

use App\Models\User;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\Response;

class UserDataExportService
{
    /**
     * Compile complete user data archive compliant with GDPR Article 20.
     */
    public function compileUserData(User $user): array
    {
        $projects = Project::where('user_id', $user->id)
            ->with([
                'context',
                'workflow.stages',
                'versions',
            ])
            ->get();

        $creditAccount = $user->creditAccount;
        $transactions = $creditAccount
            ? $creditAccount->transactions()->orderByDesc('created_at')->get()
            : collect();

        $consents = $user->consentRecords ?? collect();

        $organizations = $user->organizations()
            ->with('creditAccount')
            ->get();

        $apiKeys = $user->apiKeys()
            ->select(['id', 'organization_id', 'name', 'prefix', 'abilities', 'last_used_at', 'expires_at', 'created_at'])
            ->get();

        return [
            'export_metadata' => [
                'platform' => 'FORGE Intelligence Platform',
                'version' => '1.0',
                'exported_at' => now()->toIso8601String(),
                'standard' => 'GDPR Article 20 / CCPA Data Portability',
            ],
            'user_profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'plan' => $user->plan ?? 'free',
                'created_at' => $user->created_at?->toIso8601String(),
            ],
            'privacy_consents' => $consents->map(fn ($c) => [
                'consent_type' => is_object($c->consent_type) ? $c->consent_type->value : $c->consent_type,
                'granted' => $c->granted,
                'version' => $c->version,
                'granted_at' => $c->granted_at?->toIso8601String(),
                'revoked_at' => $c->revoked_at?->toIso8601String(),
            ]),
            'credits_and_billing' => [
                'current_balance' => $creditAccount?->balance ?? 0,
                'lifetime_granted' => $creditAccount?->lifetime_granted ?? 0,
                'lifetime_consumed' => $creditAccount?->lifetime_consumed ?? 0,
                'transactions' => $transactions->map(fn ($t) => [
                    'type' => is_object($t->type) ? $t->type->value : $t->type,
                    'amount' => $t->amount,
                    'balance_after' => $t->balance_after,
                    'description' => $t->description,
                    'created_at' => $t->created_at?->toIso8601String(),
                ]),
            ],
            'organizations' => $organizations->map(fn ($org) => [
                'id' => $org->id,
                'name' => $org->name,
                'role' => $org->pivot->role ?? 'member',
                'plan' => $org->plan,
                'joined_at' => $org->pivot->created_at ?? null,
            ]),
            'api_keys' => $apiKeys,
            'projects' => $projects->map(fn (Project $p) => [
                'id' => $p->id,
                'title' => $p->title,
                'description' => $p->description,
                'status' => is_object($p->status) ? $p->status->value : $p->status,
                'project_type' => is_object($p->project_type) ? $p->project_type->value : $p->project_type,
                'created_at' => $p->created_at?->toIso8601String(),
                'updated_at' => $p->updated_at?->toIso8601String(),
                'context' => $p->context ? [
                    'problem_statement' => $p->context->problem_statement,
                    'target_audience' => $p->context->target_audience,
                    'constraints' => $p->context->constraints,
                    'website_url' => $p->context->website_url,
                ] : null,
                'workflow_stages' => $p->workflow ? $p->workflow->stages->map(fn ($s) => [
                    'stage_type' => is_object($s->stage_type) ? $s->stage_type->value : $s->stage_type,
                    'status' => is_object($s->status) ? $s->status->value : $s->status,
                    'is_approved' => $s->is_approved ?? false,
                    'output_document' => $s->output_document,
                    'completed_at' => $s->completed_at?->toIso8601String(),
                ]) : [],
                'versions' => $p->versions->map(fn ($v) => [
                    'stage_type' => $v->stage_type,
                    'version_number' => $v->version_number,
                    'note' => $v->note,
                    'created_at' => $v->created_at?->toIso8601String(),
                ]),
            ]),
        ];
    }

    /**
     * Stream user data as a formatted downloadable JSON file.
     */
    public function exportDownload(User $user): Response
    {
        $data = $this->compileUserData($user);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $filename = sprintf('forge_user_data_export_%d_%s.json', $user->id, now()->format('Y-m-d_His'));

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            'Cache-Control' => 'no-cache, private',
        ]);
    }
}
