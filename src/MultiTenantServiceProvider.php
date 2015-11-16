<?php

namespace Laraflock\MultiTenant;

use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Support\ServiceProvider;
use Laraflock\MultiTenant\Commands\Migrate\InstallCommand;
use Laraflock\MultiTenant\Commands\Migrate\MigrateCommand;
use Laraflock\MultiTenant\Commands\Migrate\MigrateMakeCommand;
use Laraflock\MultiTenant\Commands\Migrate\RefreshCommand;
use Laraflock\MultiTenant\Commands\Migrate\ResetCommand;
use Laraflock\MultiTenant\Commands\Migrate\RollbackCommand;
use Laraflock\MultiTenant\Commands\Migrate\StatusCommand;
use Laraflock\MultiTenant\Commands\SetupCommand;

class MultiTenantServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot()
    {
        /*
         * Set configuration variables
         */
        $this->mergeConfigFrom(__DIR__ . '/../config/multi-tenant.php', 'multi-tenant');
        /*
         * Publish migrations
         */
        $this->publishes([__DIR__ . '/../database/migrations/' => database_path('/migrations')], 'migrations');

        /*
         * Bind tenancy into container
         */
        new TenancyEnvironment($this->app);

        /*
         * Register middleware to detect hostname and redirect if required
         */
        if (config('multi-tenant.hostname-detection-middleware')) {
            $this->app->make('Illuminate\Contracts\Http\Kernel')->prependMiddleware('Laraflock\MultiTenant\Middleware\HostnameMiddleware');
        }

        /*
         * Model observers
         */
        $this->observers();

        /*
         * override the default migrate command
         */
        $this->app->booted(function ($app) {
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
        Models\Tenant::observe(new Observers\TenantObserver());
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
                $app->make('Laraflock\MultiTenant\Contracts\HostnameRepositoryContract'),
                $app->make('Laraflock\MultiTenant\Contracts\WebsiteRepositoryContract'),
                $app->make('Laraflock\MultiTenant\Contracts\TenantRepositoryContract')
            );
        });

        /*
         * Register commands
         */
        $this->commands([
            SetupCommand::class,
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
            'Laraflock\MultiTenant\Contracts\DirectoryContract',
            'Laraflock\MultiTenant\Contracts\WebsiteRepositoryContract',
            'Laraflock\MultiTenant\Contracts\HostnameRepositoryContract',
        ]);
    }

    /**
     * Register all of the migration commands.
     *
     * @return void
     */
    protected function registerCommands($app)
    {
        $this->app = $app;

        $app->registerDeferredProvider(MigrationServiceProvider::class);

        $commands = ['Migrate', 'Rollback', 'Reset', 'Refresh', 'Install', 'Make', 'Status'];

        // We'll simply spin through the list of commands that are migration related
        // and register each one of them with an application container. They will
        // be resolved in the Artisan start file and registered on the console.
        foreach ($commands as $command) {
            $this->{'register' . $command . 'Command'}();
        }
    }

    /**
     * Register the "migrate" migration command.
     *
     * @return void
     */
    protected function registerMigrateCommand()
    {
        $this->app->bind('command.migrate', function ($object, $app) {
            return new MigrateCommand($this->app->make('migrator'));
        });
    }

    /**
     * Register the "rollback" migration command.
     *
     * @return void
     */
    protected function registerRollbackCommand()
    {
        $this->app->bind('command.migrate.rollback', function ($object, $app) {
            return new RollbackCommand($this->app->make('migrator'));
        });
    }

    /**
     * Register the "reset" migration command.
     *
     * @return void
     */
    protected function registerResetCommand()
    {
        $this->app->bind('command.migrate.reset', function ($object, $app) {
            return new ResetCommand($this->app->make('migrator'));
        });
    }

    /**
     * Register the "refresh" migration command.
     *
     * @return void
     */
    protected function registerRefreshCommand()
    {
        $this->app->bind('command.migrate.refresh', function ($object, $app) {
            return new RefreshCommand();
        });
    }

    /**
     * Register the "status" migration command.
     *
     * @return void
     */
    protected function registerStatusCommand()
    {
        $this->app->bind('command.migrate.status', function ($object, $app) {
            return new StatusCommand($this->app->make('migrator'));
        });
    }

    /**
     * Register the "install" migration command.
     *
     * @return void
     */
    protected function registerInstallCommand()
    {
        $this->app->bind('command.migrate.install', function ($object, $app) {
            return new InstallCommand($this->app->make('migration.repository'));
        });
    }

    /**
     * Register the "make" migration command.
     *
     * @return void
     */
    protected function registerMakeCommand()
    {
        $this->app->bind('command.migrate.make', function ($object, $app) {
            // Once we have the migration creator registered, we will create the command
            // and inject the creator. The creator is responsible for the actual file
            // creation of the migrations, and may be extended by these developers.
            $creator = $this->app->make('migration.creator');

            $composer = $this->app->make('composer');

            return new MigrateMakeCommand($creator, $composer);
        });
    }
}
