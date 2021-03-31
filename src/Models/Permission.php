<?php

namespace HuangYi\Rbac\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use SoftDeletes;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->table = Config::get('rbac.tables.permissions');
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::saved(function (self $permission) {
            $permission->forgetCaches();
        });

        self::deleted(function (self $permission) {
            $permission->forgetCaches();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        $permissionRoleTable = Config::get('rbac.tables.permission_role');

        return $this->belongsToMany(Permission::class, $permissionRoleTable, 'role_id', 'permission_id');
    }

    /**
     * Forget caches.
     */
    public function forgetCaches()
    {
        if (Config::get('rbac.cache.enable')) {
            Cache::tags('permission_role')->flush();
        }
    }
}
