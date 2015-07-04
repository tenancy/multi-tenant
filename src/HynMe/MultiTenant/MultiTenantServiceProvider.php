<?php namespace HynMe\MultiTenant;

use Illuminate\Support\ServiceProvider;
use HynMe\MultiTenant\Commands\SetupCommand;

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
         * Register commands
         */
        $this->commands([
            SetupCommand::class,
        ]);
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

}
