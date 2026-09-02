<?php

declare(strict_types=1);

namespace App\Modules\Consent\Models;

use App\Models\User;
use App\Modules\Consent\Enums\ConsentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsentRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'consent_type',
        'granted',
        'version',
        'ip_address',
        'granted_at',
        'revoked_at',
    ];

    protected $casts = [
        'consent_type' => ConsentType::class,
        'granted' => 'boolean',
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isGranted(): bool
    {
        return $this->granted && $this->revoked_at === null;
    }
}
