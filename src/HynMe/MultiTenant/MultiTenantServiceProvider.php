<?php namespace HynMe\MultiTenant;

use Illuminate\Support\ServiceProvider;

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
		return [
            'HynMe\MultiTenant\Contracts\DirectoryContract',
            'HynMe\MultiTenant\Contracts\WebsiteRepositoryContract',
            'HynMe\MultiTenant\Contracts\HostnameRepositoryContract',
        ];
	}

}
