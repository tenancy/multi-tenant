<?php

namespace Hyn\Tenancy;

use Hyn\Tenancy\Commands\Migrate\InstallCommand;
use Hyn\Tenancy\Commands\Migrate\MigrateCommand;
use Hyn\Tenancy\Commands\Migrate\MigrateMakeCommand;
use Hyn\Tenancy\Commands\Migrate\RefreshCommand;
use Hyn\Tenancy\Commands\Migrate\ResetCommand;
use Hyn\Tenancy\Commands\Migrate\RollbackCommand;
use Hyn\Tenancy\Commands\Migrate\StatusCommand;
use Hyn\Tenancy\Commands\Seeds\SeedCommand;
use Hyn\Tenancy\Commands\SetupCommand;
use Hyn\Tenancy\Commands\TenantCommand;
use Hyn\Tenancy\Contracts\CustomerRepositoryContract;
use Hyn\Tenancy\Contracts\DirectoryContract;
use Hyn\Tenancy\Contracts\HostnameRepositoryContract;
use Hyn\Tenancy\Contracts\WebsiteRepositoryContract;
use Hyn\Tenancy\Middleware\HostnameMiddleware;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Database\SeedServiceProvider;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot()
    {
        /*
         * Set configuration variables
         */
        $this->mergeConfigFrom(__DIR__ . '/../../config/multi-tenant.php', 'multi-tenant');
        $this->publishes([__DIR__ . '/../../config/multi-tenant.php' => config_path('multi-tenant.php')], 'multi-tenant-config');
        /*
         * Publish migrations
         */
        $this->publishes([__DIR__ . '/../../database/migrations/' => database_path('/migrations')], 'migrations');

        /*
         * Bind tenancy into container
         */
        new TenancyEnvironment($this->app);

        /*
         * Register middleware to detect hostname and redirect if required
         */
        if (config('multi-tenant.hostname-detection-middleware')) {
            $this->app->make(Kernel::class)
                ->prependMiddleware(HostnameMiddleware::class);
        }

        /*
         * Model observers
         */
        $this->observers();

        /*
         * override the default migrate command
         */
        $this->app->booted(function (Application $app) {
            $this->registerCommands($app);
        });
        /*
         * Add helper functions
         */
        require_once __DIR__ . '/Helpers/HelperFunctions.php';
    }

    /**
     * Registers model observers.
     */
    protected function observers()
    {
        Models\Website::observe(new Observers\WebsiteObserver());
        Models\Hostname::observe(new Observers\HostnameObserver());
        Models\Customer::observe(new Observers\CustomerObserver());
    }

    /**
     * Register all of the migration commands.
     *
     * @param $app
     */
    protected function registerCommands($app)
    {
        $this->app = $app;

        $app->registerDeferredProvider(MigrationServiceProvider::class);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        /*
         * Bind setup command into ioc
         */
        $this->app->bind(SetupCommand::class, function ($app) {
            return new SetupCommand(
                $app->make(HostnameRepositoryContract::class),
                $app->make(WebsiteRepositoryContract::class),
                $app->make(CustomerRepositoryContract::class)
            );
        });

        /*
         * Register commands
         */
        $this->commands([
            SetupCommand::class,
            TenantCommand::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array_merge($this->commands, [
            'tenant.view',
            'tenant.hostname',
            DirectoryContract::class,
            WebsiteRepositoryContract::class,
            HostnameRepositoryContract::class,
        ]);
    }
}
