<?php

declare(strict_types=1);

namespace App\Modules\Geography\Models;

use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Market extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'target_geography',
        'tam_estimate',
        'sam_estimate',
        'som_estimate',
        'key_drivers',
        'barriers_to_entry',
    ];

    protected function casts(): array
    {
        return [
            'key_drivers' => 'array',
            'barriers_to_entry' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
