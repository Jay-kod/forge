<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Decision extends Model
{
    protected $fillable = [
        'project_id',
        'workflow_stage_id',
        'question',
        'options',
        'selected_option',
        'rationale',
        'status',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'decided_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(WorkflowStage::class, 'workflow_stage_id');
    }
}
