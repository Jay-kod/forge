<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Billing\Models\Plan;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Identity\Enums\TechnicalLevel;
use App\Modules\Identity\Enums\UserRole;
use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Enums\WorkflowMode;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Plans
        $freePlan = Plan::create([
            'slug' => 'free',
            'name' => 'Free Explorer',
            'price_monthly' => 0.00,
            'price_annual' => 0.00,
            'credits_monthly' => 25,
            'is_active' => true,
            'features' => [
                '1 active workspace',
                'Basic situation classification',
                'Core market discovery report',
                'Basic PRD generator',
                'Watermarked PDF export',
            ],
        ]);

        $proPlan = Plan::create([
            'slug' => 'pro',
            'name' => 'Pro Builder',
            'price_monthly' => 39.00,
            'price_annual' => 390.00,
            'credits_monthly' => 200,
            'is_active' => true,
            'features' => [
                'Unlimited active workspaces',
                'Deep multi-source market research',
                'Full competitor intelligence matrix',
                'Evidence-linked PRD & Architecture',
                'AI Development Package export (AGENTS.md, CLAUDE.md)',
                'Clean PDF blueprint exports',
                'Continuous opportunity tracking',
            ],
        ]);

        $businessPlan = Plan::create([
            'slug' => 'business',
            'name' => 'Business & Team',
            'price_monthly' => 99.00,
            'price_annual' => 990.00,
            'credits_monthly' => 500,
            'is_active' => true,
            'features' => [
                'Everything in Pro Builder',
                'Up to 5 collaborative team seats',
                'Shared workspace & credit pools',
                'Website & codebase audit workflows',
                'Digital transformation roadmaps',
                'Export to GitHub repositories',
            ],
        ]);

        // 2. Seed Capabilities / Entitlements
        $capabilities = [
            'project.create' => ['free' => 'limit:1', 'pro' => 'unlimited', 'business' => 'unlimited'],
            'project.archive' => ['free' => 'true', 'pro' => 'true', 'business' => 'true'],
            'research.basic' => ['free' => 'true', 'pro' => 'true', 'business' => 'true'],
            'research.deep' => ['free' => 'false', 'pro' => 'true', 'business' => 'true'],
            'prd.generate' => ['free' => 'true', 'pro' => 'true', 'business' => 'true'],
            'prd.evidence_linked' => ['free' => 'false', 'pro' => 'true', 'business' => 'true'],
            'architecture.generate' => ['free' => 'basic', 'pro' => 'full', 'business' => 'full'],
            'package.generate' => ['free' => 'false', 'pro' => 'true', 'business' => 'true'],
            'export.pdf.clean' => ['free' => 'false', 'pro' => 'true', 'business' => 'true'],
            'export.package' => ['free' => 'false', 'pro' => 'true', 'business' => 'true'],
            'workflow.automatic' => ['free' => 'false', 'pro' => 'true', 'business' => 'true'],
            'workflow.page_by_page' => ['free' => 'true', 'pro' => 'true', 'business' => 'true'],
        ];

        foreach ($capabilities as $cap => $vals) {
            $freePlan->entitlements()->create(['capability' => $cap, 'value' => $vals['free']]);
            $proPlan->entitlements()->create(['capability' => $cap, 'value' => $vals['pro']]);
            $businessPlan->entitlements()->create(['capability' => $cap, 'value' => $vals['business']]);
        }

        // 3. Seed Demo Founder User
        $user = User::firstOrCreate(
            ['email' => 'founder@forge.local'],
            [
                'name' => 'Adaeze Founder',
                'role' => UserRole::USER,
                'technical_level' => TechnicalLevel::VIBE_CODER,
                'email_verified_at' => now(),
            ]
        );

        // Grant initial credits
        app(GrantCreditsAction::class)->execute(
            user: $user,
            amount: 200,
            referenceType: 'welcome_grant',
            description: 'Initial Pro allocation credits'
        );

        // Attach Pro subscription
        $user->subscription()->create([
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        // 4. Seed a Sample Workspace
        app(CreateProjectAction::class)->execute(
            user: $user,
            userInput: 'I want to build an AI product intelligence platform that helps founders discover what is possible before they code.',
            title: 'FORGE Intelligence Platform',
            mode: WorkflowMode::PAGE_BY_PAGE
        );
    }
}
