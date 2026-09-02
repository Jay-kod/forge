<?php

declare(strict_types=1);

namespace App\Modules\Research\Models;

use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteAnalysis extends Model
{
    protected $fillable = [
        'project_id',
        'url',
        'status',
        'meta_title',
        'meta_description',
        'headings',
        'performance_hints',
        'ux_score',
        'seo_score',
        'conversion_score',
        'conversion_findings',
        'recommendations',
    ];

    protected function casts(): array
    {
        return [
            'headings' => 'array',
            'performance_hints' => 'array',
            'conversion_findings' => 'array',
            'recommendations' => 'array',
            'ux_score' => 'integer',
            'seo_score' => 'integer',
            'conversion_score' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
