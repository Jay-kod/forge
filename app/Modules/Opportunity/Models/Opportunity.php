<?php

declare(strict_types=1);

namespace App\Modules\Opportunity\Models;

use App\Modules\Evidence\Enums\ConfidenceLevel;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opportunity extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'category',
        'impact',
        'difficulty',
        'confidence',
        'confidence_score',
        'status',
    ];

    protected $appends = [
        'quadrant',
        'priority_score',
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

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class);
    }

    /**
     * Map Impact and Difficulty into the 2x2 Eisenhower/Action Matrix.
     */
    public function getQuadrantAttribute(): string
    {
        $isHighImpact = in_array($this->impact, ['high', 'critical'], true);
        $isLowDifficulty = in_array($this->difficulty, ['low', 'medium'], true);

        if ($isHighImpact && $isLowDifficulty) {
            return 'quick_wins';
        }

        if ($isHighImpact && !$isLowDifficulty) {
            return 'major_projects';
        }

        if (!$isHighImpact && $isLowDifficulty) {
            return 'fill_ins';
        }

        return 'thankless_tasks';
    }

    /**
     * Calculate deterministic numerical priority score (1 - 100).
     */
    public function getPriorityScoreAttribute(): int
    {
        $impactWeight = match ($this->impact) {
            'critical' => 10,
            'high' => 8,
            'medium' => 5,
            default => 2,
        };

        $difficultyWeight = match ($this->difficulty) {
            'low' => 1,
            'medium' => 2,
            'high' => 3,
            default => 4,
        };

        $confidence = (float) ($this->confidence_score ?? 0.85);

        // Priority = (Impact / Difficulty) * Confidence * 10
        $rawScore = ($impactWeight / $difficultyWeight) * $confidence * 10;

        return (int) round(min(100, max(1, $rawScore)));
    }
}
