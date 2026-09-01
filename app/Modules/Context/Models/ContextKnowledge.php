<?php

declare(strict_types=1);

namespace App\Modules\Context\Models;

use App\Modules\Context\Enums\KnowledgeClassification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContextKnowledge extends Model
{
    protected $table = 'context_knowledge';

    protected $fillable = [
        'project_context_id',
        'field',
        'value',
        'classification',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'classification' => KnowledgeClassification::class,
        ];
    }

    public function context(): BelongsTo
    {
        return $this->belongsTo(ProjectContext::class, 'project_context_id');
    }
}
