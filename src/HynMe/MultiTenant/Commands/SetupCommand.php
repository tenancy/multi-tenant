<?php namespace HynMe\MultiTenant\Commands;

use DB, Config, File;
use HynMe\MultiTenant\Contracts\HostnameRepositoryContract;
use HynMe\MultiTenant\Contracts\TenantRepositoryContract;
use HynMe\MultiTenant\Contracts\WebsiteRepositoryContract;
use HynMe\MultiTenant\MultiTenantServiceProvider;
use Illuminate\Console\Command;
use HynMe\Webserver\Helpers\ServerConfigurationHelper;

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
        TenantRepositoryContract $tenant)
    {
        parent::__construct();

        $this->hostname = $hostname;
        $this->website = $website;
        $this->tenant = $tenant;

        $this->helper = new ServerConfigurationHelper;
    }



    /**
     * Handles the set up
     */
    public function handle()
    {

        $this->configuration = Config::get('webserver');

        $this->comment('Welcome to hyn multi tenancy.');
        $this->comment('First off, migrations for the packages will run.');

        $this->runMigrations();


        $this->comment('In the following steps you will be asked to set up your first tenant website.');

        $tenantDirectory = Config::get('multi-tenant.tenant-directory') ? Config::get('multi-tenant.tenant-directory') : storage_path('multi-tenant');


        if(!File::isDirectory($tenantDirectory) && File::makeDirectory($tenantDirectory, 0755, true))
        {
            $this->comment("The directory to hold your tenant websites has been created under {$tenantDirectory}.");
        }

        $name = $this->option('tenant');
        $email = $this->option('email');
        $hostname = $this->option('hostname');
//        $name = $this->ask($this->step++ . ': Please name your first tenant, this by default would be your company or your name.');
//        $email = $this->ask($this->step++ . ': What is the email address for this tenant?');
//        $hostname = $this->ask($this->step++ . ': What is the primary hostname you want to use for multi tenancy? Please note this hostname needs to point to the IP address of this server.');

        $webservice = null;

        /*
         * Setup webserver
         */
        if($this->helper)
        {
            $this->comment('In the next steps we will ask you about webserver configuration.');
            $webservice = $this->option('webserver');
            if($webservice != 'no')
            {
                $webserviceConfiguration = array_get($this->configuration, $webservice);
                $webserviceClass = array_get($webserviceConfiguration, 'class')
            }
            else
                $webservice = null;

            /*
             * Create the first tenant configurations
             */

            DB::beginTransaction();

            $tenant = $this->tenant->create(compact('name', 'email'));

            $website = $this->website->create(['tenant_id' => $tenant->id, 'identifier' => str_replace(['.'], '-', $hostname)]);

            $host = $this->hostname->create(['hostname' => $hostname, 'website_id' => $website->id, 'tenant_id' => $tenant->id]);

            DB::commit();

            // hook into the webservice of choice once object creation succeeded
            if(isset($webserviceClass))
                (new $webserviceClass($website))->register();


            if($tenant->exists && $website->exists && $host->exists)
                $this->info("Configuration succesful");
        }
        else
            $this->comment('The hyn-me/webserver package is not installed. Visit http://hyn.me/packages/webserver for more information.');
    }

    protected function runMigrations()
    {
        foreach(Config::get('hyn.packages', []) as $name => $package)
        {

            if(class_exists(array_get($package, 'service-provider'))) {
                $this->call('vendor:publish', [
                    '--provider' => array_get($package, 'service-provider'),
                    '-n'
                ]);
            }
        }
        $this->call('migrate', [
            '--database' => 'hyn',
            '-n'
        ]);
    }
}