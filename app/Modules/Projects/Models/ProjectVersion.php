<?php

declare(strict_types=1);

namespace App\Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'version',
        'snapshot',
        'created_by',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'version' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
