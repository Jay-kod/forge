<?php

declare(strict_types=1);

namespace App\Modules\GitHub\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GitHubConnection extends Model
{
    protected $table = 'github_connections';

    protected $fillable = [
        'user_id',
        'github_user_id',
        'github_username',
        'avatar_url',
        'access_token',
        'refresh_token',
        'scope',
        'token_type',
        'expires_at',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
