<?php namespace HynMe\MultiTenant;

use Config;
use HynMe\MultiTenant\Helpers\TenancyRequestHelper;
use HynMe\MultiTenant\Models\Hostname;
use HynMe\MultiTenant\Models\Website;
use HynMe\MultiTenant\Repositories\HostnameRepository;
use HynMe\MultiTenant\Repositories\WebsiteRepository;
use HynMe\MultiTenant\Tenant\DatabaseConnection;
use HynMe\MultiTenant\Tenant\Directory;

class TenancyEnvironment
{

    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var \HynMe\MultiTenant\Models\Hostname
     */
    protected $hostname;

    /**
     * @var \HynMe\MultiTenant\Models\Website
     */
    protected $website;

    public function __construct($app)
    {

        $this->app = $app;

        // bind tenancy environment into IOC
        $this->setupBinds();

        // load hostname object or default
        $this->hostname = TenancyRequestHelper::hostname($this->app->make('HynMe\MultiTenant\Contracts\HostnameRepositoryContract'));
        $this->website = $this->hostname->website;

        // sets the database connection for the tenant website
        DatabaseConnection::setup($this->hostname);

        // register binds for tenant website
        $this->setupTenantBinds();

        $this->app->make('HynMe\MultiTenant\Contracts\DirectoryContract');
    }

    /**
     * Binds all interfaces to the IOC container
     */
    protected function setupBinds()
    {
        $this->app->bind('HynMe\MultiTenant\Contracts\HostnameRepositoryContract', function()
        {
            return new HostnameRepository(new Hostname);
        });
        $this->app->bind('HynMe\MultiTenant\Contracts\WebsiteRepositoryContract', function($app)
        {
            return new WebsiteRepository(new Website, $this->app->make('HynMe\MultiTenant\Contracts\HostnameRepositoryContract'));
        });
    }

    /**
     * Binds all tenant specific interfaces into the IOC container
     */
    protected function setupTenantBinds()
    {
        $hostname = $this->hostname;
        $app = $this->app;

        $this->app->singleton('HynMe\MultiTenant\Contracts\DirectoryContract', function() use ($hostname, $app)
        {
            return (new Directory($hostname))->registerPaths($app);
        });
    }
}