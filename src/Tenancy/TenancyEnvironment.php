<?php

namespace Hyn\MultiTenant;

use Hyn\Tenancy\Helpers\TenancyRequestHelper;
use Hyn\Tenancy\Tenant\View as TenantView;
use View;

/**
 * Class TenancyEnvironment.
 *
 * Sets the tenant environment; overrules laravel core and sets the database connection
 */
class TenancyEnvironment
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * @var \Hyn\Tenancy\Models\Hostname
     */
    protected $hostname;

    /**
     * @var \Hyn\Tenancy\Models\Website
     */
    protected $website;

    public function __construct($app)
    {
        // share the application
        $this->app = $app;

        // sets file access to as wide as possible, ignoring server masks
        umask(0);

        // bind tenancy environment into IOC
        $this->setupBinds();

        // load hostname object or default
        $this->hostname = TenancyRequestHelper::hostname(
            $this->app->make(Contracts\HostnameRepositoryContract::class)
        );

        // set website
        $this->website = ! is_null($this->hostname) ? $this->hostname->website : null;

        // sets the database connection for the tenant website
        if (! is_null($this->website)) {
            $this->website->database->setCurrent();
        }

        // register tenant IOC bindings
        $this->setupTenantBinds();

        // register tenant paths for website
        if (! is_null($this->website)) {
            $this->app->make(Contracts\DirectoryContract::class)->registerPaths($app);
        }

        // register view shares
        View::composer('*', Composers\TenantComposer::class);
    }

    /**
     * Binds all interfaces to the IOC container.
     */
    protected function setupBinds()
    {
        /*
         * Tenant repository
         */
        $this->app->bind(Contracts\CustomerRepositoryContract::class, function () {
            return new Repositories\CustomerRepository(new Models\Customer());
        });
        /*
         * Tenant hostname repository
         */
        $this->app->bind(Contracts\HostnameRepositoryContract::class, function () {
            return new Repositories\HostnameRepository(new Models\Hostname());
        });
        /*
         * Tenant website repository
         */
        $this->app->bind(Contracts\WebsiteRepositoryContract::class, function ($app) {
            return new Repositories\WebsiteRepository(
                new Models\Website(),
                $this->app->make(Contracts\HostnameRepositoryContract::class)
            );
        });
    }

    /**
     * Binds all tenant specific interfaces into the IOC container.
     */
    protected function setupTenantBinds()
    {
        $hostname = $this->hostname;

        /*
         * Tenant directory mapping and functionality
         */
        $this->app->singleton(Contracts\DirectoryContract::class, function () use ($hostname) {
            return $hostname ? new Tenant\Directory($hostname->website) : null;
        });

        /*
         * Tenant view shares
         */
        $this->app->singleton('tenant.view', function () use ($hostname) {
            return new TenantView([
                'hostname' => $hostname,
            ]);
        });

        /*
         * Tenant hostname
         */
        $this->app->singleton('tenant.hostname', function () use ($hostname) {
            return $hostname;
        });
    }
}
