<?php

declare(strict_types=1);

namespace App\Modules\AI\Models;

use App\Models\User;
use App\Modules\Organizations\Models\Organization;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ByokCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'organization_id',
        'provider',
        'api_key',
        'label',
        'is_active',
        'last_validated_at',
    ];

    protected $hidden = [
        'api_key',
    ];

    protected $appends = [
        'masked_key',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'is_active' => 'boolean',
        'last_validated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function getMaskedKeyAttribute(): string
    {
        $raw = $this->api_key;
        if (!$raw || strlen($raw) < 8) {
            return '••••••••';
        }

        $prefix = substr($raw, 0, 4);
        $suffix = substr($raw, -4);

        return "{$prefix}••••••••{$suffix}";
    }
}
