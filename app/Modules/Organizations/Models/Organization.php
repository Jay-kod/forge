<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Models;

use App\Models\User;
use App\Modules\Credits\Models\CreditAccount;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'plan',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function creditAccount(): HasOne
    {
        return $this->hasOne(OrganizationCreditAccount::class);
    }

    public function hasMember(User $user): bool
    {
        if ($this->owner_id === $user->id) {
            return true;
        }

        return $this->members()->where('users.id', $user->id)->exists();
    }

    public function getRole(User $user): ?string
    {
        if ($this->owner_id === $user->id) {
            return 'owner';
        }

        return $this->members()->where('users.id', $user->id)->first()?->pivot?->role;
    }

    public function hasRole(User $user, array|string $roles): bool
    {
        $role = $this->getRole($user);
        if (!$role) {
            return false;
        }

        if (is_string($roles)) {
            $roles = [$roles];
        }

        return in_array($role, $roles, true);
    }
}
