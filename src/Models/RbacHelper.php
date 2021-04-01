<?php

namespace HuangYi\Rbac\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

trait RbacHelper
{
    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $cachedRoles;

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $cachedPermissions;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        $roleUserTable = Config::get('rbac.tables.role_user');

        return $this->belongsToMany(Role::class, $roleUserTable, 'user_id', 'role_id');
    }

    /**
     * Attach role.
     *
     * @param \HuangYi\Rbac\Models\Role|int|string $role
     */
    public function attachRole($role)
    {
        $this->roles()->attach($role);
    }

    /**
     * Attach roles.
     *
     * @param \Illuminate\Database\Eloquent\Collection|array $roles
     */
    public function attachRoles($roles)
    {
        $this->roles()->attach($roles);
    }

    /**
     * Detach role.
     *
     * @param \HuangYi\Rbac\Models\Role|int|string $role
     */
    public function detachRole($role)
    {
        $this->roles()->detach($role);
    }

    /**
     * Detach roles.
     *
     * @param \Illuminate\Database\Eloquent\Collection|array $roles
     */
    public function detachRoles($roles)
    {
        $this->roles()->detach($roles);
    }

    /**
     * Sync the roles.
     *
     * @param mixed $roles
     * @return array
     */
    public function syncRoles($roles)
    {
        return $this->roles()->sync($roles);
    }

    /**
     * Sync the roles without detaching.
     *
     * @param mixed $roles
     * @return array
     */
    public function syncRolesWithoutDetaching($roles)
    {
        return $this->roles()->syncWithoutDetaching($roles);
    }

    /**
     * If the use has a role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        return $this->cached_roles->where('slug', $role)->count() > 0;
    }

    /**
     * If the use has all/any roles.
     *
     * @param array $roles
     * @param bool $all
     * @return bool
     */
    public function hasRoles(array $roles, $all = true)
    {
        $slugs = $this->cached_roles->pluck('slug')->toArray();

        $intersects = array_intersect($slugs, $roles);

        if ($all) {
            return count($intersects) == count($roles);
        }

        return count($intersects) > 0;
    }

    /**
     * If the use has a permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        return $this->cached_permissions->where('slug', $permission)->count() > 0;
    }

    /**
     * If the use has all/any permissions.
     *
     * @param array $permissions
     * @param bool $all
     * @return bool
     */
    public function hasPermissions(array $permissions, $all = true)
    {
        $slugs = $this->cached_permissions->pluck('slug')->toArray();

        $intersects = array_intersect($slugs, $permissions);

        if ($all) {
            return count($intersects) == count($permissions);
        }

        return count($intersects) > 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCachedRolesAttribute()
    {
        if ($this->cachedRoles instanceof Collection) {
            return $this->cachedRoles;
        }

        if (Config::get('rbac.cache.enable')) {
            $cacheKey = sprintf('rbac:user:%s:roles', $this->getKey());
            $cacheTtl = Config::get('rbac.cache.ttl');

            $this->cachedRoles = Cache::tags('role_user')->remember($cacheKey, $cacheTtl, function () {
                return $this->roles()->get();
            });
        } else {
            $this->cachedRoles = $this->roles()->get();
        }

        return $this->cachedRoles;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCachedPermissionsAttribute()
    {
        if ($this->cachedPermissions instanceof Collection) {
            return $this->cachedPermissions;
        }

        $permissions = Collection::make();

        if (Config::get('rbac.cache.enable')) {
            $this->cached_roles->each(function ($role) use (&$permissions) {
                $permissions = $permissions->merge($role->cached_permissions);
            });
        } else {
            $this->cached_roles->load('permissions');
            $this->cached_roles->each(function ($role) use (&$permissions) {
                $permissions = $permissions->merge($role->permissions);
            });
        }

        return $this->cachedPermissions = $permissions->unique();
    }
}
