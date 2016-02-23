<?php

namespace Hyn\MultiTenant\Commands;

use File;
use Hyn\Framework\Exceptions\TenantPropertyException;
use Hyn\Webserver\Helpers\ServerConfigurationHelper;
use Illuminate\Console\Command;
use Hyn\MultiTenant\Contracts\HostnameRepositoryContract;
use Hyn\MultiTenant\Contracts\TenantRepositoryContract;
use Hyn\MultiTenant\Contracts\WebsiteRepositoryContract;
use Hyn\MultiTenant\Tenant\DatabaseConnection;

class SetupCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'multi-tenant:setup
        {--tenant= : Name of the first tenant}
        {--email= : Email address of the first tenant}
        {--hostname= : Domain- or hostname for the first tenant website}
        {--webserver= : Hook into webserver (nginx|apache|no)}';

    /**
     * @var string
     */
    protected $description = 'Final configuration step for hyn multi tenancy packages';

    /**
     * @var ServerConfigurationHelper
     */
    protected $helper;

    /**
     * @var HostnameRepositoryContract
     */
    protected $hostname;
    /**
     * @var WebsiteRepositoryContract
     */
    protected $website;
    /**
     * @var TenantRepositoryContract
     */
    protected $tenant;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var int
     */
    protected $step = 1;

    /**
     * @param HostnameRepositoryContract $hostname
     * @param WebsiteRepositoryContract  $website
     * @param TenantRepositoryContract   $tenant
     */
    public function __construct(
        HostnameRepositoryContract $hostname,
        WebsiteRepositoryContract $website,
        TenantRepositoryContract $tenant
    ) {
        parent::__construct();

        $this->hostname = $hostname;
        $this->website  = $website;
        $this->tenant   = $tenant;

        $this->helper = new ServerConfigurationHelper();
    }

    /**
     * Handles the set up.
     */
    public function handle()
    {
        $this->configuration = config('webserver');

        $name     = $this->option('tenant');
        $email    = $this->option('email');
        $hostname = $this->option('hostname');

        if (empty($name)) {
            $name = $this->ask('Please provide a tenant name or restart command with --tenant');
        }

        if (empty($email)) {
            $email = $this->ask('Please provide a tenant email address or restart command with --email');
        }

        if (empty($hostname)) {
            $hostname = $this->ask('Please provide a tenant hostname or restart command with --hostname');
        }

        $this->comment('Welcome to hyn multi tenancy.');

        $this->publishFiles();

        // If the dashboard is installed we need to prevent default laravel migrations
        // so we run the dashboard setup command before running any migrations
        if (class_exists('Hyn\ManagementInterface\ManagementInterfaceServiceProvider')) {
            $this->info('The management interface will be installed first.');
            $this->call('dashboard:setup');
        } else {

            $this->comment('First off, migrations for the packages will run.');

            // Migrations are run during dashboard setup or here.
            $this->runMigrations();
        }

        $tenantDirectory = config('multi-tenant.tenant-directory') ? config('multi-tenant.tenant-directory') : storage_path('multi-tenant');

        if (!File::isDirectory($tenantDirectory) && File::makeDirectory($tenantDirectory, 0755, true)) {
            $this->comment("The directory to hold your tenant websites has been created under {$tenantDirectory}.");
        }

        $webserver = null;

        // Setup webserver
        if ($this->helper) {

            $this->helper->createDirectories();

            $webserver = $this->option('webserver') ?: 'no';

            if ($webserver != 'no') {
                $webserverConfiguration = array_get($this->configuration, $webserver);
                $webserverClass         = array_get($webserverConfiguration, 'class');
            } else {
                $webserver = null;
            }

            // Create the first tenant configurations
            $tenant = $this->tenant->create(compact('name', 'email'));

            $identifier = substr(str_replace(['.'], '-', $hostname), 0, 10);

            $website = $this->website->create(['tenant_id' => $tenant->id, 'identifier' => $identifier]);

            $host = $this->hostname->create([
                'hostname'   => $hostname,
                'website_id' => $website->id,
                'tenant_id'  => $tenant->id,
            ]);

            // hook into the webservice of choice once object creation succeeded
            if ($webserver) {
                (new $webserverClass($website))->register();
            }

            if ($tenant->exists && $website->exists && $host->exists) {
                $this->info('Configuration successful');
            }
        } else {
            $this->error('The hyn/webserver package is not installed. Visit http://hyn.me/packages/webserver for more information.');
        }
    }

    /**
     * Publish files for all Hyn packages.
     */
    protected function publishFiles()
    {
        foreach (config('hyn.packages', []) as $name => $package) {
            if (class_exists(array_get($package, 'service-provider'))) {
                $this->call('vendor:publish', [
                    '--provider' => array_get($package, 'service-provider'),
                    '-n',
                ]);
            }
        }
    }

    /**
     * Run migrations for all depending service providers.
     */
    protected function runMigrations()
    {
        $this->call('migrate', [
            '--database' => DatabaseConnection::systemConnectionName(),
            '-n',
        ]);
    }
}
