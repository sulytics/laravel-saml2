<?php

namespace Freegee\LaravelSaml2;

use Freegee\LaravelSaml2\Commands\CreateIdentityProvider;
use Freegee\LaravelSaml2\Commands\CreateServiceProvider;
use Illuminate\Support\Facades\Log;

class Saml2ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected bool $defer = false;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootMiddleware();
        $this->bootRoutes();
        $this->bootPublishes();
        $this->bootCommands();
        $this->loadMigrations();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap the console commands.
     *
     * @return void
     */
    protected function bootMiddleware()
    {
        $this->app['router']->aliasMiddleware('saml2.resolveIdentityProvider', Http\Middleware\ResolveIdentityProvider::class);
    }

    /**
     * Bootstrap the routes.
     *
     * @return void
     */
    protected function bootRoutes()
    {
        if(config('saml2_settings.useRoutes', false)) {
            include __DIR__ . '/../routes/routes.php';
        }
    }

    /**
     * Bootstrap the publishable files.
     *
     * @return void
     */
    protected function bootPublishes(): void
    {
        $this->publishes([
            __DIR__ . '/../config/saml2_settings.php' => config_path('saml2_settings.php'),
        ]);
    }

    /**
     * Bootstrap the console commands.
     *
     * @return void
     */
    protected function bootCommands(): void
    {
        $this->commands([
            CreateServiceProvider::class,
            CreateIdentityProvider::class,
        ]);
    }

    /**
     * Load the package migrations.
     *
     * @return void
     */
    protected function loadMigrations(): void
    {
        $path = __DIR__ . '/../database/migrations';
        $this->loadMigrationsFrom([$path]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}