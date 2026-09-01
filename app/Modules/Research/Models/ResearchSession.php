<?php

declare(strict_types=1);

namespace App\Modules\Research\Models;

use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResearchSession extends Model
{
    protected $fillable = [
        'project_id',
        'type',
        'status',
        'started_at',
        'completed_at',
        'credits_consumed',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'credits_consumed' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sources(): HasMany
    {
        return $this->hasMany(ResearchSource::class);
    }
}
