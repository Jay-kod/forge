<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Models\User;
use App\Modules\Product\Enums\WorkflowStageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStage extends Model
{
    protected $fillable = [
        'workflow_id',
        'stage_type',
        'order',
        'status',
        'content',
        'approved_at',
        'approved_by',
        'version',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'stage_type' => WorkflowStageType::class,
            'content' => 'array',
            'order' => 'integer',
            'version' => 'integer',
            'approved_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(Decision::class);
    }
}
