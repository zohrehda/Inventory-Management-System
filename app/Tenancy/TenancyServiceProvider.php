<?php

namespace App\Tenancy;

use App\Tenancy\Middleware\InitializeTenancy;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/tenancy.php', 'tenancy');
        $this->app->singleton(Tenancy::class);
        $this->app->singleton(TenantResolver::class);
    }

    public function boot(Router $router): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/lang');
        $router->aliasMiddleware('tenant', InitializeTenancy::class);
    }
}