<?php namespace Laraflock\MultiTenant\Tests;


use File, DB;
use HynMe\Framework\Testing\TestCase;
use Illuminate\Database\Connection;
use Laraflock\MultiTenant\MultiTenantServiceProvider;

class TenancySetupTest extends TestCase
{
    /**
     * @covers \Laraflock\MultiTenant\MultiTenantServiceProvider
     */
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
     *
     * @covers \Laraflock\MultiTenant\Commands\SetupCommand
     * @covers \Laraflock\MultiTenant\Tenant\DatabaseConnection::create
     * @covers \Laraflock\MultiTenant\Tenant\Directory::create
     * @covers \Laraflock\MultiTenant\Observers\WebsiteObserver::created
     * @covers \Laraflock\MultiTenant\Observers\WebsiteObserver::creating
     * @covers \Laraflock\MultiTenant\Observers\HostnameObserver::saved
     *
     * @covers \HmtTenantsTable
     * @covers \HmtWebsitesTable
     * @covers \HmtHostnamesTable
     */
    public function testCommand()
    {
        // create first tenant
        $this->assertEquals(0, $this->artisan('multi-tenant:setup', [
            '--tenant' => 'example',
            '--hostname' => 'system.testing',    // configured in travis as primary hostname
            '--email' => 'info@example.org',
            '--webserver' => 'no'
        ]));


    }

    /**
     * @depends testCommand
     * @covers \Laraflock\MultiTenant\Repositories\TenantRepository::findByName
     * @covers \Laraflock\MultiTenant\Contracts\TenantRepositoryContract::findByName
     */
    public function testTenantExistence()
    {
        /** @var \Laraflock\MultiTenant\Contracts\TenantRepositoryContract tenant */
        $this->tenant = $this->app->make('Laraflock\MultiTenant\Contracts\TenantRepositoryContract');
        /** @var \Laraflock\MultiTenant\Models\Tenant|null $tenant */
        $tenant = $this->tenant->findByName('example');

        $this->assertNotNull($tenant, 'Tenant from command has not been created');
    }

    /**
     * @depends testTenantExistence
     * @covers \Laraflock\MultiTenant\Contracts\HostnameRepositoryContract::findByHostname
     * @covers \Laraflock\MultiTenant\Repositories\HostnameRepository::findByHostname
     */
    public function testHostnameExistence()
    {
        /** @var \Laraflock\MultiTenant\Contracts\HostnameRepositoryContract hostname */
        $this->hostname = $this->app->make('Laraflock\MultiTenant\Contracts\HostnameRepositoryContract');

        /** @var \Laraflock\MultiTenant\Models\Hostname|null $hostname */
        $hostname = $this->hostname->findByHostname('system.testing');

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
     * @covers \Laraflock\MultiTenant\Tenant\Directory
     */
    public function testTenantFoldersExist()
    {
        /** @var \Laraflock\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('Laraflock\MultiTenant\Contracts\HostnameRepositoryContract');
        /** @var \Laraflock\MultiTenant\Models\Hostname|null $website */
        $hostname = $this->hostname->findByHostname('system.testing');

        /** @var \Laraflock\MultiTenant\Models\Website $website */
        $website = $hostname->website;

        foreach($website->directory->pathsToCreate() as $directory)
        {
            // let's check whether the directory has been succesfully created
            $this->assertTrue(File::exists($website->directory->{$directory}()));
            // directories are created with 0755; let's see whether that is sufficient
            $this->assertTrue(File::isWritable($website->directory->{$directory}()));
        }
    }

    /**
     * @depends testTenantDatabaseExists
     * @covers \Laraflock\MultiTenant\Tenant\DatabaseConnection
     */
    public function testTenantConnection()
    {
        /** @var \Laraflock\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('Laraflock\MultiTenant\Contracts\HostnameRepositoryContract');
        /** @var \Laraflock\MultiTenant\Models\Hostname|null $website */
        $hostname = $this->hostname->findByHostname('system.testing');

        /** @var \Laraflock\MultiTenant\Tenant\DatabaseConnection $connection */
        $connection = $hostname->website->database;

        $connection->setCurrent();
        $this->assertTrue($connection->isCurrent());
        $this->assertEquals($connection->name, $connection->getCurrent());
        $this->assertTrue($connection->get() instanceof Connection);

    }

    /**
     * @depends testTenantDatabaseExists
     * @covers \Laraflock\MultiTenant\Commands\Migrate\InstallCommand
     * @covers \Laraflock\MultiTenant\Commands\Migrate\MigrateCommand
     * @covers \TestTenantMigration
     */
    public function testTenantMigrationRuns()
    {
        $this->assertEquals(0, $this->artisan('migrate', [
            '--tenant' => 'all',
            '--path' => 'vendor/laraflock/multi-tenant/tests/database/migrations/',
            '--force'
        ]));
    }


    /**
     * @depends testTenantMigrationRuns
     * @covers \Laraflock\MultiTenant\Commands\Migrate\MigrateCommand
     */
    public function testTenantMigratedTableExists()
    {
        /** @var \Laraflock\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('Laraflock\MultiTenant\Contracts\HostnameRepositoryContract');
        /** @var \Laraflock\MultiTenant\Models\Hostname|null $website */
        $hostname = $this->hostname->findByHostname('system.testing');

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
     * @covers \Laraflock\MultiTenant\Commands\Migrate\MigrateCommand
     * @covers \Laraflock\MultiTenant\Tenant\DatabaseConnection::setCurrent
     */
    public function testTenantMigrationEntryExists()
    {
        /** @var \Laraflock\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('Laraflock\MultiTenant\Contracts\HostnameRepositoryContract');
        /** @var \Laraflock\MultiTenant\Models\Hostname|null $website */
        $hostname = $this->hostname->findByHostname('system.testing');

        if(!$hostname)
            throw new \Exception("Unit test hostname not found");

        $hostname->website->database->setCurrent();

        foreach(File::allFiles(__DIR__ . '/database/migrations') as $file)
        {
            $fileBaseName = $file->getBaseName('.'.$file->getExtension());
            $this->seeInDatabase('migrations', ['migration' => $fileBaseName], $hostname->website->database->name);
        }
    }

    /**
     * @depends testTenantExistence
     * @covers \Laraflock\MultiTenant\Middleware\HostnameMiddleware
     */
    public function testMiddleware()
    {

        /** @var \Laraflock\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('Laraflock\MultiTenant\Contracts\HostnameRepositoryContract');
        /** @var \Laraflock\MultiTenant\Models\Hostname|null $website */
        $hostname = $this->hostname->findByHostname('system.testing');

        $this->visit('http://tenant.testing/')->seeStatusCode(200)->seeJson([
            'hostname' => null
        ]);
        $this->visit('http://system.testing/')->seeStatusCode(200)->seeJson([
            'hostname' => $hostname
        ]);
    }
}