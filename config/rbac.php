<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Defines the rbac table names.
    |
    */

    'tables' => [
        'permissions'     => 'permissions',
        'roles'           => 'roles',
        'permission_role' => 'permission_role',
        'role_user'       => 'role_user',
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    |
    | Specifies the user model class. And this model must use
    | the 'HuangYi\Rbac\Models\RbacHelper' trait.
    |
    */

    'user' => App\User::class,

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | This option relies on the 'illuminate/cache' package.
    |
    */

    'cache' => [

        'enable' => env('RBAC_CACHE', false),

        'ttl' => env('RBAC_CACHE_TTL', 3600),

    ],
];
