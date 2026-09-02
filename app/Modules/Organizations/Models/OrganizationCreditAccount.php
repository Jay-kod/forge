<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrganizationCreditAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'balance',
        'lifetime_granted',
        'lifetime_consumed',
    ];

    protected $casts = [
        'balance' => 'integer',
        'lifetime_granted' => 'integer',
        'lifetime_consumed' => 'integer',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(OrganizationCreditTransaction::class);
    }
}
