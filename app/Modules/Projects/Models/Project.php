<?php

declare(strict_types=1);

namespace App\Modules\Projects\Models;

use App\Models\User;
use App\Modules\Context\Models\ProjectContext;
use App\Modules\Discovery\Models\Competitor;
use App\Modules\Discovery\Models\Discovery;
use App\Modules\Evidence\Models\Evidence;
use App\Modules\Geography\Models\Market;
use App\Modules\Opportunity\Models\Opportunity;
use App\Modules\Opportunity\Models\Recommendation;
use App\Modules\Product\Models\ProductDocument;
use App\Modules\Product\Models\Workflow;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Enums\WorkflowMode;
use App\Modules\Research\Models\ResearchSession;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'classification',
        'status',
        'workflow_mode',
        'current_stage',
    ];

    protected function casts(): array
    {
        return [
            'classification' => ProjectType::class,
            'status' => ProjectStatus::class,
            'workflow_mode' => WorkflowMode::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function context(): HasOne
    {
        return $this->hasOne(ProjectContext::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ProjectVersion::class);
    }

    public function discovery(): HasOne
    {
        return $this->hasOne(Discovery::class);
    }

    public function competitors(): HasMany
    {
        return $this->hasMany(Competitor::class);
    }

    public function researchSessions(): HasMany
    {
        return $this->hasMany(ResearchSession::class);
    }

    public function evidence(): HasMany
    {
        return $this->hasMany(Evidence::class);
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    public function workflow(): HasOne
    {
        return $this->hasOne(Workflow::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProductDocument::class);
    }

    public function markets(): HasMany
    {
        return $this->hasMany(Market::class);
    }

    public function websiteAnalysis(): HasOne
    {
        return $this->hasOne(\App\Modules\Research\Models\WebsiteAnalysis::class);
    }
}
