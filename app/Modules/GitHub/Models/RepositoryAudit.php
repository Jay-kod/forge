<?php

declare(strict_types=1);

namespace App\Modules\GitHub\Models;

use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RepositoryAudit extends Model
{
    protected $table = 'repository_audits';

    protected $fillable = [
        'project_id',
        'repo_full_name',
        'default_branch',
        'primary_language',
        'detected_framework',
        'architecture_pattern',
        'file_count',
        'manifests',
        'code_health_score',
        'technical_debt_score',
        'security_score',
        'raw_metrics',
    ];

    protected function casts(): array
    {
        return [
            'file_count' => 'integer',
            'manifests' => 'array',
            'code_health_score' => 'integer',
            'technical_debt_score' => 'integer',
            'security_score' => 'integer',
            'raw_metrics' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
