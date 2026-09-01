<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Modules\Projects\Enums\WorkflowMode;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    protected $fillable = [
        'project_id',
        'mode',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'mode' => WorkflowMode::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function stages(): HasMany
    {
        return $this->hasMany(WorkflowStage::class)->orderBy('order');
    }
}
