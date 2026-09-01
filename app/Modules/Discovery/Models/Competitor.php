<?php

declare(strict_types=1);

namespace App\Modules\Discovery\Models;

use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Competitor extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'url',
        'description',
        'category',
        'strengths',
        'weaknesses',
        'pricing',
        'target_market',
        'differentiation',
        'source_ids',
    ];

    protected function casts(): array
    {
        return [
            'strengths' => 'array',
            'weaknesses' => 'array',
            'pricing' => 'array',
            'source_ids' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
