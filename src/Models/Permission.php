<?php

namespace HuangYi\Rbac\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'slug', 'description'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->table = Config::get('rbac.tables.permissions');

        parent::__construct($attributes);
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

        self::deleting(function (self $permission) {
            $permission->roles()->sync([]);
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

        return $this->belongsToMany(Role::class, $permissionRoleTable, 'permission_id', 'role_id');
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
