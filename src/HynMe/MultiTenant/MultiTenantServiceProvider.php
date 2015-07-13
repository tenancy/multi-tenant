<?php namespace HynMe\MultiTenant;

use Illuminate\Database\MigrationServiceProvider;
use Illuminate\Support\ServiceProvider;
use HynMe\MultiTenant\Commands\SetupCommand;
use HynMe\MultiTenant\Commands\Migrate\MigrateCommand;
use HynMe\MultiTenant\Commands\Migrate\RollbackCommand;
use HynMe\MultiTenant\Commands\Migrate\RefreshCommand;
use HynMe\MultiTenant\Commands\Migrate\ResetCommand;
use HynMe\MultiTenant\Commands\Migrate\StatusCommand;
use HynMe\MultiTenant\Commands\Migrate\MigrateMakeCommand;
use HynMe\MultiTenant\Commands\Migrate\InstallCommand;

class MultiTenantServiceProvider extends ServiceProvider {

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
        $this->mergeConfigFrom(__DIR__.'/../../config/multi-tenant.php', 'multi-tenant');
        /*
         * Publish migrations
         */
        $this->publishes([__DIR__.'/../../migrations/' => database_path('/migrations')], 'migrations');


        /*
         * Bind tenancy into container
         */
        new TenancyEnvironment($this->app);

        /*
         * Register middleware to detect hostname and redirect if required
         */
        $this->app->make('Illuminate\Contracts\Http\Kernel')->prependMiddleware('HynMe\MultiTenant\Middleware\HostnameMiddleware');

        /*
         * Model observers
         */
        $this->observers();
    }

    /**
     * Registers model observers
     */
    protected function observers()
    {
        Models\Website::observe(new Observers\WebsiteObserver);
        Models\Hostname::observe(new Observers\HostnameObserver);
        Models\Tenant::observe(new Observers\TenantObserver);
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
        $this->app->bind(SetupCommand::class, function($app)
        {
            return new SetupCommand(
                $app->make('HynMe\MultiTenant\Contracts\HostnameRepositoryContract'),
                $app->make('HynMe\MultiTenant\Contracts\WebsiteRepositoryContract'),
                $app->make('HynMe\MultiTenant\Contracts\TenantRepositoryContract')
            );
        });


        /*
         * Force loading the MigrationServiceProvider from the framework
         *
         */
        $this->app->register(MigrationServiceProvider::class);
        /*
         * override the default migrate command
         */
        $this->registerCommands();

        /*
         * Register commands
         */
        $this->commands([
            'command.migrate', 'command.migrate.make',
            'command.migrate.install', 'command.migrate.rollback',
            'command.migrate.reset', 'command.migrate.refresh',
            'command.migrate.status',
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
            'HynMe\MultiTenant\Contracts\DirectoryContract',
            'HynMe\MultiTenant\Contracts\WebsiteRepositoryContract',
            'HynMe\MultiTenant\Contracts\HostnameRepositoryContract',
        ]);
	}

    /**
     * Register all of the migration commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        $commands = ['Migrate', 'Rollback', 'Reset', 'Refresh', 'Install', 'Make', 'Status'];

        // We'll simply spin through the list of commands that are migration related
        // and register each one of them with an application container. They will
        // be resolved in the Artisan start file and registered on the console.
        foreach ($commands as $command) {
            $this->{'register'.$command.'Command'}();
        }
    }

    /**
     * Register the "migrate" migration command.
     *
     * @return void
     */
    protected function registerMigrateCommand()
    {
        $this->app->extend('command.migrate', function ($object, $app) {
            return new MigrateCommand($app['migrator']);
        });
    }

    /**
     * Register the "rollback" migration command.
     *
     * @return void
     */
    protected function registerRollbackCommand()
    {
        $this->app->singleton('command.migrate.rollback', function ($object, $app) {
            return new RollbackCommand($app['migrator']);
        });
    }

    /**
     * Register the "reset" migration command.
     *
     * @return void
     */
    protected function registerResetCommand()
    {
        $this->app->extend('command.migrate.reset', function ($object, $app) {
            return new ResetCommand($app['migrator']);
        });
    }

    /**
     * Register the "refresh" migration command.
     *
     * @return void
     */
    protected function registerRefreshCommand()
    {
        $this->app->extend('command.migrate.refresh', function ($object, $app) {
            return new RefreshCommand;
        });
    }

    protected function registerStatusCommand()
    {
        $this->app->extend('command.migrate.status', function ($object, $app) {
            return new StatusCommand($app['migrator']);
        });
    }

    /**
     * Register the "install" migration command.
     *
     * @return void
     */
    protected function registerInstallCommand()
    {
        $this->app->extend('command.migrate.install', function ($object, $app) {
            return new InstallCommand($app['migration.repository']);
        });
    }

    /**
     * Register the "make" migration command.
     *
     * @return void
     */
    protected function registerMakeCommand()
    {

        $this->app->extend('command.migrate.make', function ($object, $app) {
            // Once we have the migration creator registered, we will create the command
            // and inject the creator. The creator is responsible for the actual file
            // creation of the migrations, and may be extended by these developers.
            $creator = $app['migration.creator'];

            $composer = $app['composer'];

            return new MigrateMakeCommand($creator, $composer);
        });
    }

}
