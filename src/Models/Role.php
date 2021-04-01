<?php

namespace HuangYi\Rbac\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class Role extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'description'];

    /**
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $cachedPermissions;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = Config::get('rbac.tables.roles');
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::saved(function (self $role) {
            $role->forgetCaches();
        });

        self::deleting(function (self $role) {
            $role->permissions()->sync([]);
            $role->users()->sync([]);
        });

        self::deleted(function (self $role) {
            $role->forgetCaches();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        $user = Config::get('rbac.user');
        $roleUserTable = Config::get('rbac.tables.role_user');

        return $this->belongsToMany($user, $roleUserTable, 'role_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        $permissionRoleTable = Config::get('rbac.tables.permission_role');

        return $this->belongsToMany(Permission::class, $permissionRoleTable, 'role_id', 'permission_id');
    }

    /**
     * Attach permissions.
     *
     * @param \HuangYi\Rbac\Models\Permission|int|string $permission
     */
    public function attachPermission($permission)
    {
        $this->permissions()->attach($permission);
    }

    /**
     * Attach permissions.
     *
     * @param \Illuminate\Database\Eloquent\Collection|array $permissions
     */
    public function attachPermissions($permissions)
    {
        $this->permissions()->attach($permissions);
    }

    /**
     * Detach permissions.
     *
     * @param \HuangYi\Rbac\Models\Permission|int|string $permission
     */
    public function detachPermission($permission)
    {
        $this->permissions()->detach($permission);
    }

    /**
     * Detach permissions.
     *
     * @param \Illuminate\Database\Eloquent\Collection|array $permissions
     */
    public function detachPermissions($permissions)
    {
        $this->permissions()->detach($permissions);
    }

    /**
     * Sync the permissions.
     *
     * @param mixed $permissions
     * @return array
     */
    public function syncPermissions($permissions)
    {
        return $this->permissions()->sync($permissions);
    }

    /**
     * Sync the permissions without detaching.
     *
     * @param mixed $permissions
     * @return array
     */
    public function syncPermissionsWithoutDetaching($permissions)
    {
        return $this->permissions()->syncWithoutDetaching($permissions);
    }

    /**
     * If the role has a permission.
     *
     * @param string $permission
     * @return bool
     */
    public function hasPermission($permission)
    {
        return $this->cached_permissions->where('slug', $permission)->count() > 0;
    }

    /**
     * If the role has all/any permissions.
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
    public function getCachedPermissionsAttribute()
    {
        if ($this->cachedPermissions instanceof Collection) {
            return $this->cachedPermissions;
        }

        if (Config::get('rbac.cache.enable')) {
            $cacheKey = $this->getCacheKey();
            $cacheTtl = Config::get('rbac.cache.ttl');

            $this->cachedPermissions = Cache::tags('permission_role')->remember($cacheKey, $cacheTtl, function () {
                return $this->permissions()->get();
            });
        } else {
            $this->cachedPermissions = $this->permissions()->get();
        }

        return $this->cachedPermissions;
    }

    /**
     * Get cache key.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return sprintf('rbac:role:%s:permissions', $this->getKey());
    }

    /**
     * Forget cache.
     */
    public function forgetCaches()
    {
        if (Config::get('rbac.cache.enable')) {
            Cache::forget($this->getCacheKey());
            Cache::tags('role_user')->flush();
        }
    }
}
