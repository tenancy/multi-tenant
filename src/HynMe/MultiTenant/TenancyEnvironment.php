<?php namespace HynMe\MultiTenant;

use Config;
use HynMe\MultiTenant\Helpers\TenancyRequestHelper;
use HynMe\MultiTenant\Models\Hostname;
use HynMe\MultiTenant\Models\Website;
use HynMe\MultiTenant\Repositories\HostnameRepository;
use HynMe\MultiTenant\Repositories\WebsiteRepository;

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
        $this->app->bind('HynMe\MultiTenant\Contracts\WebsiteRepositoryContract', function()
        {
            return new WebsiteRepository(new Website);
        });
    }

    /**
     * Sets config/database.php database.connections.tenant
     */
    protected function setupTenantConnection()
    {
        $clone = Config::get('database.connections.system');
        $clone['password'] = md5(env('APP_KEY') . $this->hostname->hostname);
        $clone['username'] = $clone['database'] = str_replace(['.'], '-', $this->hostname->hostname);
        Config::set('database.connections.tenant', $clone);
    }
}