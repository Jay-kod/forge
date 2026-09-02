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

    protected $appends = [
        'freshness',
        'days_old',
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

    public function getDaysOldAttribute(): int
    {
        $date = $this->completed_at ?? $this->created_at;
        return $date ? (int) $date->diffInDays(now()) : 0;
    }

    /**
     * Determine research freshness: fresh (<=30d), aging (31-90d), stale (>90d).
     */
    public function getFreshnessAttribute(): string
    {
        $days = $this->days_old;
        if ($days <= 30) {
            return 'fresh';
        }
        if ($days <= 90) {
            return 'aging';
        }
        return 'stale';
    }
}
