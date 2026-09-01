<?php

declare(strict_types=1);

namespace App\Modules\Opportunity\Models;

use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recommendation extends Model
{
    protected $fillable = [
        'project_id',
        'opportunity_id',
        'title',
        'description',
        'why_it_matters',
        'why_now',
        'potential_impact',
        'difficulty',
        'dependencies',
        'evidence_ids',
        'suggested_action',
        'status',
        'user_response',
    ];

    protected function casts(): array
    {
        return [
            'dependencies' => 'array',
            'evidence_ids' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }
}
