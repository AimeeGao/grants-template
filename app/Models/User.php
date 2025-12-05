<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'guid',
        'first_name',
        'last_name',
        'name',
        'email',
        'idir_username',
        'bcsc_username',
        'bceid_username',
        'idir_user_guid',
        'bcsc_user_guid',
        'bceid_user_guid',
        'bceid_business_guid',
        'keycloak_id',
        'identity_provider',
        'display_name',
        'given_name',
        'family_name',
        'organization',
        'is_active',
        'kc_token',
        'kc_refresh_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the roles for the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the student profile associated with the user.
     */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class, 'user_guid', 'guid');
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if user can manage institutions.
     */
    public function canManageInstitutions(): bool
    {
        return $this->hasAnyRole(Role::getInstitutionManagerRoles());
    }
    
    /**
     * Check if user can manage admin users.
     */
    public function canManageAdminUsers(): bool
    {
        return $this->hasAnyRole([Role::SUPER_ADMIN, Role::ADMIN_MANAGER, Role::SECURITY_OFFICER]);
    }

    /**
     * Check if user has admin privileges.
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(Role::getAdminRoles());
    }

    /**
     * Get the institution associated with this user (for BCeID users).
     */
    public function institution()
    {
        if ($this->identity_provider === 'bceid' && $this->bceid_business_guid) {
            return \Modules\Institution\Models\Institution::where('bceid_business_guid', $this->bceid_business_guid)->first();
        }
        return null;
    }

    /**
     * Get user's identity provider display name.
     */
    public function getIdentityProviderDisplayName(): string
    {
        return match ($this->identity_provider) {
            'idir' => 'IDIR',
            'bceid' => 'BCeID',
            'bcsc' => 'BC Services Card',
            default => 'Unknown',
        };
    }
}
