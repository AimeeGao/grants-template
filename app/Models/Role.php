<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the users that belong to the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * role constants
     */
    public const MINISTRY_ADMIN = 'Ministry Admin';
    public const MINISTRY_USER = 'Ministry User';
    public const INSTITUTION_USER = 'Institution User';
    public const INSTITUTION_ADMIN = 'Institution Admin';
    public const STUDENT = 'Student';

    // Admin roles - only accessible via /admin/login
    public const SUPER_ADMIN = 'Super Admin';
    public const ADMIN_MANAGER = 'Admin Manager';
    public const APPLICATION_MANAGER = 'Application Manager';
    public const SECURITY_OFFICER = 'Security Officer';
    public const PRIVACY_OFFICER = 'Privacy Officer';
    public const ADMIN_GUEST = 'Admin Guest';

    /**
     * Get all available roles.
     */
    public static function getAvailableRoles(): array
    {
        return [
            self::SUPER_ADMIN => 'Super Administrator',
            self::ADMIN_MANAGER => 'Admin Manager',
            self::APPLICATION_MANAGER => 'Application Manager',
            self::SECURITY_OFFICER => 'Security Officer',
            self::PRIVACY_OFFICER => 'Privacy Officer',
            self::ADMIN_GUEST => 'Admin Guest',

            self::INSTITUTION_USER => 'Institution User',
            self::INSTITUTION_ADMIN => 'Institution Administrator',

            self::STUDENT => 'Student',

            self::MINISTRY_ADMIN => 'Ministry Administrator',
            self::MINISTRY_USER => 'Ministry User',
        ];
    }

    /**
     * Get roles that can manage institutions.
     */
    public static function getInstitutionManagerRoles(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN_MANAGER,
        ];
    }

    /**
     * Get all admin roles.
     */
    public static function getAdminRoles(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN_MANAGER,
            self::APPLICATION_MANAGER,
            self::SECURITY_OFFICER,
            self::PRIVACY_OFFICER,
            self::ADMIN_GUEST,
            self::MINISTRY_ADMIN,
            self::MINISTRY_USER
        ];
    }

    /**
     * Check if a role can manage institutions.
     */
    public static function canManageInstitutions(string $roleName): bool
    {
        return in_array($roleName, self::getInstitutionManagerRoles());
    }

    public static function redirectPath(string $role): string
    {
        return match ($role) {
            self::MINISTRY_ADMIN,
            self::MINISTRY_USER   => '/ministry',

            self::INSTITUTION_USER,
            self::INSTITUTION_ADMIN => '/institution',

            self::STUDENT         => '/student',

            self::SUPER_ADMIN,
            self::ADMIN_MANAGER,
            self::APPLICATION_MANAGER,
            self::SECURITY_OFFICER,
            self::PRIVACY_OFFICER => '/admin',

            default               => '/', // fallback
        };
    }
}
