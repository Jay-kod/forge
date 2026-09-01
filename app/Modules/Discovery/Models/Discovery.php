<?php

declare(strict_types=1);

namespace App\Modules\Discovery\Models;

use App\Modules\Discovery\Enums\DiscoveryVerdict;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Discovery extends Model
{
    protected $fillable = [
        'project_id',
        'verdict',
        'summary',
        'rationale',
    ];

    protected function casts(): array
    {
        return [
            'verdict' => DiscoveryVerdict::class,
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
