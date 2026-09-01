<?php

declare(strict_types=1);

namespace App\Modules\Research\Models;

use App\Modules\Evidence\Models\Evidence;
use App\Modules\Research\Enums\SourceType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ResearchSource extends Model
{
    protected $fillable = [
        'research_session_id',
        'url',
        'title',
        'source_type',
        'publication_date',
        'retrieved_at',
        'content_summary',
        'reliability_score',
    ];

    protected function casts(): array
    {
        return [
            'source_type' => SourceType::class,
            'publication_date' => 'date',
            'retrieved_at' => 'datetime',
            'reliability_score' => 'decimal:2',
        ];
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ResearchSession::class, 'research_session_id');
    }

    public function evidence(): BelongsToMany
    {
        return $this->belongsToMany(Evidence::class, 'evidence_source_links')
            ->withPivot('relevance')
            ->withTimestamps();
    }
}
