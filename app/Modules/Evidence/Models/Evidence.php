<?php

declare(strict_types=1);

namespace App\Modules\Evidence\Models;

use App\Modules\Evidence\Enums\ConfidenceLevel;
use App\Modules\Projects\Models\Project;
use App\Modules\Research\Models\ResearchSource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Evidence extends Model
{
    protected $table = 'evidence';

    protected $fillable = [
        'project_id',
        'claim',
        'confidence',
        'confidence_score',
        'category',
    ];

    protected $appends = [
        'freshness',
        'days_old',
    ];

    protected function casts(): array
    {
        return [
            'confidence' => ConfidenceLevel::class,
            'confidence_score' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(ResearchSource::class, 'evidence_source_links')
            ->withPivot('relevance')
            ->withTimestamps();
    }

    public function getDaysOldAttribute(): int
    {
        return $this->created_at ? (int) $this->created_at->diffInDays(now()) : 0;
    }

    /**
     * Determine evidence freshness: fresh (<=30d), aging (31-90d), stale (>90d).
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
