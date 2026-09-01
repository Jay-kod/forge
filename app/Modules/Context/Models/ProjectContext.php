<?php

declare(strict_types=1);

namespace App\Modules\Context\Models;

use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectContext extends Model
{
    protected $fillable = [
        'project_id',
        'user_input',
        'classification',
        'classification_confidence',
        'user_understanding',
        'business_context',
        'product_context',
        'geographic_context',
        'existing_system',
        'goals',
    ];

    protected function casts(): array
    {
        return [
            'classification_confidence' => 'decimal:2',
            'user_understanding' => 'array',
            'business_context' => 'array',
            'product_context' => 'array',
            'geographic_context' => 'array',
            'existing_system' => 'array',
            'goals' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function knowledge(): HasMany
    {
        return $this->hasMany(ContextKnowledge::class);
    }
}
