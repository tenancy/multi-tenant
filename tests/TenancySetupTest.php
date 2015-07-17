<?php namespace LaraLeague\MultiTenant\Tests;


use File, DB;
use HynMe\Framework\Testing\TestCase;
use LaraLeague\MultiTenant\MultiTenantServiceProvider;

class TenancySetupTest extends TestCase
{
    public function testPackages()
    {
        $this->assertTrue(class_exists('HynMe\Framework\FrameworkServiceProvider'), 'Class FrameworkServiceProvider does not exist');
        $this->assertNotFalse($this->app->make('hyn.package.multi-tenant'), 'packages are not loaded through FrameworkServiceProvider');

        $this->assertTrue(in_array(MultiTenantServiceProvider::class, $this->app->getLoadedProviders()), 'MultiTenantService provider is not loaded in Laravel');
        $this->assertTrue($this->app->isBooted());

        $this->assertNotFalse($this->app->make('hyn.package.multi-tenant'));
    }

    /**
     * @depends testPackages
     */
    public function testCommand()
    {
        // create first tenant
        $this->assertEquals(0, $this->artisan('multi-tenant:setup', [
            '--tenant' => 'example',
            '--hostname' => 'example.org',
            '--email' => 'info@example.org',
            '--webserver' => 'no'
        ]));


    }

    /**
     * @depends testCommand
     */
    public function testTenantExistence()
    {
        /** @var \LaraLeague\MultiTenant\Contracts\TenantRepositoryContract tenant */
        $this->tenant = $this->app->make('LaraLeague\MultiTenant\Contracts\TenantRepositoryContract');
        /** @var \LaraLeague\MultiTenant\Models\Tenant|null $tenant */
        $tenant = $this->tenant->findByName('example');

        $this->assertNotNull($tenant, 'Tenant from command has not been created');
    }

    /**
     * @depends testTenantExistence
     */
    public function testHostnameExistence()
    {
        /** @var \LaraLeague\MultiTenant\Contracts\HostnameRepositoryContract hostname */
        $this->hostname = $this->app->make('LaraLeague\MultiTenant\Contracts\HostnameRepositoryContract');

        /** @var \LaraLeague\MultiTenant\Models\Hostname|null $hostname */
        $hostname = $this->hostname->findByHostname('example.org');

        $this->assertNotNull($hostname, 'Hostname from command has not been created');

    }

    /**
     * @depends testTenantExistence
     */
    public function testTenantDatabaseExists()
    {
        $databases = DB::connection('hyn')->select('SHOW DATABASES');

        $found = false;
        $list = [];

        foreach($databases as $database)
        {
            if(substr($database->Database,0,1) == 1)
                $found = true;

            $list[] = $database->Database;
        }

        $this->assertTrue($found, "Databases found: " . implode(', ', $list));
    }

    /**
     * @depends testTenantDatabaseExists
     */
    public function testTenantMigrationRuns()
    {
        $this->assertEquals(0, $this->artisan('migrate', [
            '--tenant' => 'all',
            '--path' => 'vendor/lara-league/multi-tenant/tests/database/migrations/',
            '--force'
        ]));
    }


    /**
     * @depends testTenantMigrationRuns
     */
    public function testTenantMigratedTableExists()
    {
        /** @var \LaraLeague\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('LaraLeague\MultiTenant\Contracts\HostnameRepositoryContract');
        /** @var \LaraLeague\MultiTenant\Models\Hostname|null $website */
        $hostname = $this->hostname->findByHostname('example.org');

        $this->assertGreaterThan(0, $hostname
            ->website
            ->database
            ->get()
            ->table('tenant_migration_test')
            ->insertGetId(['some_field' => 'foo'])
        );
    }

    /**
     * @depends testTenantMigrationRuns
     */
    public function testTenantMigrationEntryExists()
    {
        /** @var \LaraLeague\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('LaraLeague\MultiTenant\Contracts\HostnameRepositoryContract');
        /** @var \LaraLeague\MultiTenant\Models\Hostname|null $website */
        $hostname = $this->hostname->findByHostname('example.org');

        if(!$hostname)
            throw new \Exception("Unit test hostname not found");

        $hostname->website->database->setCurrent();

        foreach(File::allFiles(__DIR__ . '/database/migrations') as $file)
        {
            $fileBaseName = $file->getBaseName('.'.$file->getExtension());
            $this->seeInDatabase('migrations', ['migration' => $fileBaseName], $hostname->website->database->name);
        }
    }
}