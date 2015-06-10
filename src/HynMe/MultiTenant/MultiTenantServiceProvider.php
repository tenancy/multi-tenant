<?php namespace HynMe\MultiTenant;

use Illuminate\Support\ServiceProvider;

class MultiTenantServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    protected $commands = [
        'HynMe\MultiTenant\Commands\SetupCommand'
    ];


    public function boot()
    {
        /*
         * Set configuration variables
         */
        $this->mergeConfigFrom(__DIR__.'/../../config/multi-tenant.php', 'multi-tenant');
        /*
         * Bind tenancy into container
         */
        if(env('HYN_MULTI_TENANCY_HOSTNAME', false))
        {
            new TenancyEnvironment($this->app);
        }
        /*
         * Publish migrations
         */
        $this->publishes([__DIR__.'/../../migrations/' => database_path('/migrations')], 'migrations');

        $this->app->make('Illuminate\Contracts\Http\Kernel')->prependMiddleware('HynMe\MultiTenant\Middleware\HostnameMiddleware');

        $this->observers();

        foreach($this->commands as $command)
        {
            $this->app->bind($command, function() use ($command)
            {
                return new $command;
            });
        }
        $this->commands($this->commands);
    }

    /**
     * Registers model observers
     */
    protected function observers()
    {
        Models\Website::observe(new Observers\WebsiteObserver);
        Models\Hostname::observe(new Observers\HostnameObserver);
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
