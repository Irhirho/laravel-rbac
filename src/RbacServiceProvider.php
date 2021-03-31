<?php

namespace HuangYi\Rbac;

use HuangYi\Rbac\Middleware\Rbac as RbacMiddleware;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class RbacServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $configPath = __DIR__ . '/../config/rbac.php';
        $migrationsPath = __DIR__ . '/../migrations/';

        $this->publishes([
            $configPath  => $this->app->basePath() . '/config/rbac.php',
        ], 'config');

        $this->publishes([
            $migrationsPath => $this->app->basePath() . '/database/migrations/',
        ], 'migrations');

        $this->mergeConfigFrom($configPath, 'rbac');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerDirectives();
    }

    /**
     * Register blade directives.
     */
    protected function registerDirectives()
    {
        if (! $this->app->has('view')) {
            return;
        }

        Blade::directive('ifHasRole', function ($role) {
            return "<?php if(Auth::check() && Auth::user()->hasRole({$role})): ?>";
        });

        Blade::directive('endIfHasRole', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('ifHasAnyRoles', function ($roles) {
            return "<?php if(Auth::check() && Auth::user()->hasRoles({$roles}, false)): ?>";
        });

        Blade::directive('endIfHasAnyRoles', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('ifHasAllRoles', function ($roles) {
            return "<?php if(Auth::check() && Auth::user()->hasRoles({$roles})): ?>";
        });

        Blade::directive('endIfHasAllRoles', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('ifHasPermission', function ($permission) {
            return "<?php if(Auth::check() && Auth::user()->hasPermission({$permission})): ?>";
        });

        Blade::directive('endIfHasPermission', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('ifHasAnyPermissions', function ($permissions) {
            return "<?php if(Auth::check() && Auth::user()->hasPermissions({$permissions}, false)): ?>";
        });

        Blade::directive('endIfHasAnyPermissions', function () {
            return "<?php endif; ?>";
        });

        Blade::directive('ifHasAllPermissions', function ($permissions) {
            return "<?php if(Auth::check() && Auth::user()->hasPermissions({$permissions})): ?>";
        });

        Blade::directive('endIfHasAllPermissions', function () {
            return "<?php endif; ?>";
        });
    }
}
