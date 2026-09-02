<?php

declare(strict_types=1);

namespace App\Modules\Credits\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditAccount extends Model
{
    protected $fillable = [
        'user_id',
        'organization_id',
        'balance',
        'lifetime_granted',
        'lifetime_consumed',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'lifetime_granted' => 'integer',
            'lifetime_consumed' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\Organizations\Models\Organization::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }
}
