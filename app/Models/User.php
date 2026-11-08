<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_MANAGER = 'manager';

    public const ROLE_STAFF = 'staff';

    public const ROLE_CLIENT = 'client';

    /** @var list<string> */
    public static function assignableRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_MANAGER,
            self::ROLE_STAFF,
            self::ROLE_CLIENT,
        ];
    }

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'password_plain',
        'role',
        'access_customized',
        'last_login_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'password_plain',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'access_customized' => 'boolean',
        ];
    }

    public function modulePermissions(): HasMany
    {
        return $this->hasMany(UserModulePermission::class);
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    /**
     * Whether the user has a permission / module slug.
     * Administrators always pass. Supports legacy group keys (e.g. "sales").
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Home is always available so users are never locked out of the app entry page
        if ($permission === 'dashboard') {
            return true;
        }

        $allowed = $this->effectiveAllowedSet();

        if (isset($allowed[$permission])) {
            return true;
        }

        // Asking for a legacy parent (e.g. "sales") → true if any child is allowed
        $legacy = config('permissions.legacy_groups', []);
        if (isset($legacy[$permission])) {
            foreach ($legacy[$permission] as $child) {
                if (isset($allowed[$child])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Allowed module keys as a set (key => true), expanding legacy parents.
     *
     * @return array<string, true>
     */
    public function effectiveAllowedSet(): array
    {
        if ($this->isAdmin()) {
            return array_fill_keys(array_keys(config('permissions.modules', [])), true);
        }

        /** @var array<string, list<string>> $legacy */
        $legacy = config('permissions.legacy_groups', []);
        $set = [];

        if ($this->access_customized) {
            $rows = $this->relationLoaded('modulePermissions')
                ? $this->modulePermissions
                : $this->modulePermissions()->get();

            foreach ($rows as $row) {
                if (! $row->allowed) {
                    continue;
                }
                $set[$row->module] = true;
                if (isset($legacy[$row->module])) {
                    foreach ($legacy[$row->module] as $child) {
                        $set[$child] = true;
                    }
                }
            }

            $set['dashboard'] = true;

            return $set;
        }

        $role = strtolower(trim((string) $this->role));
        $granted = config('permissions.roles.'.$role, []);

        if (in_array('*', $granted, true)) {
            return array_fill_keys(array_keys(config('permissions.modules', [])), true);
        }

        foreach ($granted as $key) {
            $set[$key] = true;
            if (isset($legacy[$key])) {
                foreach ($legacy[$key] as $child) {
                    $set[$child] = true;
                }
            }
        }

        $set['dashboard'] = true;

        return $set;
    }

    /**
     * Effective allowed module keys for this user.
     *
     * @return list<string>
     */
    public function allowedModules(): array
    {
        return array_keys($this->effectiveAllowedSet());
    }

    /**
     * Map of module => allowed for the access editor UI.
     *
     * @return array<string, bool>
     */
    public function moduleAccessMap(): array
    {
        $map = [];
        foreach (array_keys(config('permissions.modules', [])) as $key) {
            $map[$key] = $this->hasPermission($key);
        }

        return $map;
    }

    /**
     * True when the current request path is allowed for this user.
     * Unknown (uncatalogued) paths are allowed so APIs keep working.
     * Admin routes remain gated by the admin middleware separately.
     */
    public function canAccessRequest(Request $request): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        // Never let non-admins into admin area via this layer either
        if ($request->is('admin', 'admin/*')) {
            return false;
        }

        // Home / workspace landing + navbar chrome — always open for signed-in users
        if ($request->is('/', 'home', 'dashboard', 'api/navbar*')) {
            return true;
        }

        $modules = config('permissions.modules', []);
        $matchedModule = null;

        foreach ($modules as $key => $module) {
            foreach ($module['patterns'] as $pattern) {
                if ($request->is($pattern)) {
                    $matchedModule = $key;
                    break 2;
                }
            }
        }

        // Path not in catalog → allow (misc helpers)
        if ($matchedModule === null) {
            return true;
        }

        // Dashboard module permission is for sidebar label only; landing is always allowed above
        if ($matchedModule === 'dashboard') {
            return true;
        }

        return $this->hasPermission($matchedModule);
    }
}
