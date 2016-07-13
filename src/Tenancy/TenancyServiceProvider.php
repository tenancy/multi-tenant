<?php

namespace Hyn\MultiTenant;

use Hyn\Tenancy\Commands\Migrate\InstallCommand;
use Hyn\Tenancy\Commands\Migrate\MigrateCommand;
use Hyn\Tenancy\Commands\Migrate\MigrateMakeCommand;
use Hyn\Tenancy\Commands\Migrate\RefreshCommand;
use Hyn\Tenancy\Commands\Migrate\ResetCommand;
use Hyn\Tenancy\Commands\Migrate\RollbackCommand;
use Hyn\Tenancy\Commands\Migrate\StatusCommand;
use Hyn\Tenancy\Commands\Seeds\SeedCommand;
use Hyn\Tenancy\Commands\SetupCommand;
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
    protected $defer = true;

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
        $app->registerDeferredProvider(SeedServiceProvider::class);

        $commands = ['Migrate', 'Rollback', 'Reset', 'Refresh', 'Install', 'Make', 'Status', 'Seed'];

        // We'll simply spin through the list of commands that are migration related
        // and register each one of them with an application container. They will
        // be resolved in the Artisan start file and registered on the console.
        foreach ($commands as $command) {
            $this->{'register' . $command . 'Command'}();
        }
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
     * Register the "seed" command.
     *
     * @return void
     */
    protected function registerSeedCommand()
    {
        $this->app->bind('command.seed', function ($object, $app) {
            return new SeedCommand($this->app->make('db'));
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
