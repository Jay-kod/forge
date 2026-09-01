<?php

declare(strict_types=1);

namespace App\Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'price_monthly',
        'price_annual',
        'credits_monthly',
        'stripe_price_id_monthly',
        'stripe_price_id_annual',
        'is_active',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'decimal:2',
            'price_annual' => 'decimal:2',
            'credits_monthly' => 'integer',
            'is_active' => 'boolean',
            'features' => 'array',
        ];
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
