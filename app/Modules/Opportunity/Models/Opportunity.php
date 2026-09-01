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
}
