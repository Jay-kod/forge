<?php

declare(strict_types=1);

namespace App\Modules\Consent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningSignal extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'category',
        'signal_type',
        'context_meta',
        'value',
        'created_at',
    ];

    protected $casts = [
        'context_meta' => 'array',
        'value' => 'float',
        'created_at' => 'datetime',
    ];
}
