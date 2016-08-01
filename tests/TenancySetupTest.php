<?php

namespace Hyn\Tenancy\Tests;

use DB;
use File;
use Hyn\Framework\Testing\TestCase;
use Hyn\Tenancy\Contracts\CustomerRepositoryContract;
use Hyn\Tenancy\Contracts\HostnameRepositoryContract;
use Hyn\Tenancy\Contracts\TenantRepositoryContract;
use Hyn\Tenancy\MultiTenantServiceProvider;
use Hyn\Tenancy\TenancyServiceProvider;
use Hyn\Tenancy\Tenant\DatabaseConnection;
use Hyn\Tests\Seeds\TestTenantSeeder;
use Illuminate\Database\Connection;

class TenancySetupTest extends TestCase
{
    /**
     * @var TenantRepositoryContract
     */
    protected $tenant;

    /**
     * @var HostnameRepositoryContract
     */
    protected $hostname;

    /**
     * @test
     * @covers \Hyn\Tenancy\TenancyServiceProvider
     * @covers \Hyn\Framework\FrameworkServiceProvider
     * @covers \Hyn\Webserver\WebserverServiceProvider
     */
    public function verify_package_integrity()
    {
        $this->assertTrue(
            class_exists('Hyn\Framework\FrameworkServiceProvider'),
            'Class FrameworkServiceProvider does not exist'
        );
        $this->assertNotFalse(
            $this->app->make('hyn.package.multi-tenant'),
            'packages are not loaded through FrameworkServiceProvider'
        );

        $this->assertTrue(
            in_array(TenancyServiceProvider::class, $this->app->getLoadedProviders()),
            'MultiTenantService provider is not loaded in Laravel'
        );
        $this->assertTrue($this->app->isBooted());

        $this->assertNotFalse($this->app->make('hyn.package.multi-tenant'));
    }

    /**
     * @test
     * @depends verify_package_integrity
     *
     * @covers  \Hyn\Tenancy\Commands\SetupCommand
     * @covers  \Hyn\Tenancy\Tenant\DatabaseConnection::create
     * @covers  \Hyn\Tenancy\Tenant\Directory::create
     * @covers  \Hyn\Tenancy\Observers\WebsiteObserver::created
     * @covers  \Hyn\Tenancy\Observers\HostnameObserver::saved
     *
     * @covers  \HmtTenantsTable
     * @covers  \HmtWebsitesTable
     * @covers  \HmtHostnamesTable
     */
    public function can_succesfully_run_tenant_setup_command()
    {
        // create first tenant
        $this->assertEquals(
            0,
            $this->artisan(
                'multi-tenant:setup',
                [
                    '--customer'  => 'example',
                    // configured in travis as primary hostname
                    '--hostname'  => 'system.testing',
                    '--email'     => 'info@example.org',
                    '--webserver' => 'no',
                    // no interaction
                    '-n'
                ]
            )
        );
    }

    /**
     * @test
     * @depends can_succesfully_run_tenant_setup_command
     * @covers  \Hyn\Tenancy\Repositories\CustomerRepository::findByName
     * @covers  \Hyn\Tenancy\Contracts\CustomerRepositoryContract::findByName
     */
    public function tenant_should_exist()
    {
        /* @var \Hyn\Tenancy\Contracts\CustomerRepositoryContract $customers */
        $customers = $this->app->make(CustomerRepositoryContract::class);
        /** @var \Hyn\Tenancy\Models\Customer|null $customer */
        $customer = $customers->findByName('example');

        $this->assertNotNull($customer, 'Tenant from command has not been created');
    }

    /**
     * @test
     * @depends tenant_should_exist
     * @covers  \Hyn\Tenancy\Contracts\HostnameRepositoryContract::findByHostname
     * @covers  \Hyn\Tenancy\Repositories\HostnameRepository::findByHostname
     */
    public function hostname_should_exist()
    {
        $hostname = $this->loadSystemTesting();

        $this->assertNotNull($hostname, 'Hostname from command has not been created');
    }

    /**
     * @return \Hyn\Tenancy\Models\Hostname
     */
    protected function loadSystemTesting()
    {
        $this->hostname = $this->app->make('Hyn\Tenancy\Contracts\HostnameRepositoryContract');
        return $this->hostname->findByHostname('system.testing');
    }

    /**
     * @test
     * @depends tenant_should_exist
     */
    public function tenant_database_should_exist()
    {
        $databases = DB::connection(DatabaseConnection::systemConnectionName())->select('SHOW DATABASES');

        $found = false;
        $list = [];

        foreach ($databases as $database) {
            if (substr($database->Database, 0, 1) == 1) {
                $found = true;
            }

            $list[] = $database->Database;
        }

        $this->assertTrue($found, 'Databases found: ' . implode(', ', $list));
    }

    /**
     * @test
     * @depends tenant_should_exist
     * @covers  \Hyn\Tenancy\Tenant\Directory
     */
    public function tenant_folder_should_exist()
    {
        $hostname = $this->loadSystemTesting();
        /** @var \Hyn\Tenancy\Models\Website $website */
        $website = $hostname->website;

        foreach ($website->directory->pathsToCreate() as $directory) {
            // let's check whether the directory has been succesfully created
            $this->assertTrue(File::exists($website->directory->{$directory}()));
            // directories are created with 0755; let's see whether that is sufficient
            $this->assertTrue(File::isWritable($website->directory->{$directory}()));
        }
    }

    /**
     * @test
     * @depends tenant_database_should_exist
     * @covers  \Hyn\Tenancy\Tenant\DatabaseConnection
     */
    public function tenant_database_connection_should_work()
    {
        $hostname = $this->loadSystemTesting();

        /** @var \Hyn\Tenancy\Tenant\DatabaseConnection $connection */
        $connection = $hostname->website->database;

        $connection->setCurrent();
        $this->assertTrue($connection->isCurrent());
        $this->assertEquals($connection->name, $connection->getCurrent());
        $this->assertTrue($connection->get() instanceof Connection);
    }

    /**
     * @test
     * @depends tenant_database_should_exist
     * @covers  \Hyn\Tenancy\Commands\Migrate\InstallCommand
     * @covers  \Hyn\Tenancy\Commands\Migrate\MigrateCommand
     * @covers  \TestTenantMigration
     */
    public function tenant_migrations_should_run()
    {
        $this->assertEquals(
            0,
            $this->artisan(
                'migrate',
                [
                    '--tenant' => 'all',
                    '--path'   => '../../../tests/database/migrations/',
                    '--force'  => true,
                ]
            )
        );
    }

    /**
     * @test
     * @depends tenant_migrations_should_run
     * @covers  \Hyn\Tenancy\Commands\Migrate\MigrateCommand
     */
    public function tenant_migrated_table_should_exist()
    {
        $hostname = $this->loadSystemTesting();

        $this->assertGreaterThan(
            0,
            $hostname
                ->website
                ->database
                ->get()
                ->table('tenant_migration_test')
                ->insertGetId(['some_field' => 'foo'])
        );
    }

    /**
     * @test
     * @depends tenant_migrated_table_should_exist
     * @covers  \Hyn\Tenancy\Commands\Seeds\SeedCommand
     */
    public function tenant_seeder_should_work()
    {
        $hostname = $this->loadSystemTesting();

        $this->assertEquals(
            0,
            $this->artisan(
                'db:seed',
                [
                    '--tenant' => 'all',
                    '--class'  => TestTenantSeeder::class
                ]
            )
        );

        $this->assertGreaterThan(
            1,
            $hostname
                ->website
                ->database
                ->get()
                ->table('tenant_migration_test')
                ->count()
        );
    }

    /**
     * @test
     * @depends tenant_migrated_table_should_exist
     * @covers  \Hyn\Tenancy\Commands\Migrate\MigrateCommand
     * @covers  \Hyn\Tenancy\Tenant\DatabaseConnection::setCurrent
     */
    public function tenant_migration_entry_should_exist()
    {
        $hostname = $this->loadSystemTesting();

        if (!$hostname) {
            throw new \Exception('Unit test hostname not found');
        }

        $hostname->website->database->setCurrent();

        foreach (File::allFiles(__DIR__ . '/database/migrations') as $file) {
            $fileBaseName = $file->getBaseName('.' . $file->getExtension());
            $this->seeInDatabase('migrations', ['migration' => $fileBaseName], $hostname->website->database->name);
        }
    }

    /**
     * @no-test
     * @depends tenant_should_exist
     * @covers  \Hyn\Tenancy\Middleware\HostnameMiddleware
     * @todo    this actually works, but json return does not hold the hostname.
     */
    public function middleware_must_resolve_hostname()
    {
        $hostname = $this->loadSystemTesting();

        // test for unregistered hostname
        $this->visit('http://tenant.testing/tenant/view')
            ->seeStatusCode(200)
            ->seeJson(
                [
                    'hostname' => null
                ]
            );

        // test for registered hostname
        $this->visit('http://system.testing/tenant/view')
            ->seeStatusCode(200)
            ->seeJson(
                [
                    'hostname' => $hostname
                ]
            );
    }
}
