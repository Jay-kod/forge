<?php

declare(strict_types=1);

namespace App\Modules\Product\Models;

use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductDocument extends Model
{
    protected $fillable = [
        'project_id',
        'type',
        'title',
        'content',
        'version',
        'status',
        'evidence_ids',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'evidence_ids' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
