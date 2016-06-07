<?php

namespace Hyn\MultiTenant\Tests;

use DB;
use File;
use Hyn\Framework\Testing\TestCase;
use Hyn\MultiTenant\Contracts\TenantRepositoryContract;
use Illuminate\Database\Connection;
use Hyn\MultiTenant\MultiTenantServiceProvider;
use Hyn\MultiTenant\Tenant\DatabaseConnection;

class TenancySetupTest extends TestCase
{
    /**
     * @var TenantRepositoryContract
     */
    protected $tenant;

    /**
     * @covers \Hyn\MultiTenant\MultiTenantServiceProvider
     */
    public function testPackages()
    {
        $this->assertTrue(class_exists('Hyn\Framework\FrameworkServiceProvider'),
            'Class FrameworkServiceProvider does not exist');
        $this->assertNotFalse($this->app->make('hyn.package.multi-tenant'),
            'packages are not loaded through FrameworkServiceProvider');

        $this->assertTrue(in_array(MultiTenantServiceProvider::class, $this->app->getLoadedProviders()),
            'MultiTenantService provider is not loaded in Laravel');
        $this->assertTrue($this->app->isBooted());

        $this->assertNotFalse($this->app->make('hyn.package.multi-tenant'));
    }

    /**
     * @depends testPackages
     *
     * @covers  \Hyn\MultiTenant\Commands\SetupCommand
     * @covers  \Hyn\MultiTenant\Tenant\DatabaseConnection::create
     * @covers  \Hyn\MultiTenant\Tenant\Directory::create
     * @covers  \Hyn\MultiTenant\Observers\WebsiteObserver::created
     * @covers  \Hyn\MultiTenant\Observers\HostnameObserver::saved
     *
     * @covers  \HmtTenantsTable
     * @covers  \HmtWebsitesTable
     * @covers  \HmtHostnamesTable
     */
    public function testCommand()
    {
        // create first tenant
        $this->assertEquals(0, $this->artisan('multi-tenant:setup', [
            '--tenant'    => 'example',
            '--hostname'  => 'system.testing',    // configured in travis as primary hostname
            '--email'     => 'info@example.org',
            '--webserver' => 'no',
        ]));
    }

    /**
     * @depends testCommand
     * @covers  \Hyn\MultiTenant\Repositories\TenantRepository::findByName
     * @covers  \Hyn\MultiTenant\Contracts\TenantRepositoryContract::findByName
     */
    public function testTenantExistence()
    {
        /* @var \Hyn\MultiTenant\Contracts\TenantRepositoryContract tenant */
        $this->tenant = $this->app->make('Hyn\MultiTenant\Contracts\TenantRepositoryContract');
        /** @var \Hyn\MultiTenant\Models\Tenant|null $tenant */
        $tenant = $this->tenant->findByName('example');

        $this->assertNotNull($tenant, 'Tenant from command has not been created');
    }

    /**
     * @depends testTenantExistence
     * @covers  \Hyn\MultiTenant\Contracts\HostnameRepositoryContract::findByHostname
     * @covers  \Hyn\MultiTenant\Repositories\HostnameRepository::findByHostname
     */
    public function testHostnameExistence()
    {
        /* @var \Hyn\MultiTenant\Contracts\HostnameRepositoryContract hostname */
        $this->hostname = $this->app->make('Hyn\MultiTenant\Contracts\HostnameRepositoryContract');

        /** @var \Hyn\MultiTenant\Models\Hostname|null $hostname */
        $hostname = $this->hostname->findByHostname('system.testing');

        $this->assertNotNull($hostname, 'Hostname from command has not been created');
    }

    /**
     * @depends testTenantExistence
     */
    public function testTenantDatabaseExists()
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

        $this->assertTrue($found, 'Databases found: '.implode(', ', $list));
    }

    /**
     * @depends testTenantDatabaseExists
     * @covers  \Hyn\MultiTenant\Tenant\Directory
     */
    public function testTenantFoldersExist()
    {
        /* @var \Hyn\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('Hyn\MultiTenant\Contracts\HostnameRepositoryContract');
        /* @var \Hyn\MultiTenant\Models\Hostname|null $website */
        $hostname = $this->hostname->findByHostname('system.testing');
        /** @var \Hyn\MultiTenant\Models\Website $website */
        $website = $hostname->website;

        foreach ($website->directory->pathsToCreate() as $directory) {
            // let's check whether the directory has been succesfully created
            $this->assertTrue(File::exists($website->directory->{$directory}()));
            // directories are created with 0755; let's see whether that is sufficient
            $this->assertTrue(File::isWritable($website->directory->{$directory}()));
        }
    }

    /**
     * @depends testTenantDatabaseExists
     * @covers \Hyn\MultiTenant\Tenant\Directory::env
     */
    public function testTenantEnvFileOverrules()
    {
    }

    /**
     * @depends testTenantDatabaseExists
     * @covers  \Hyn\MultiTenant\Tenant\DatabaseConnection
     */
    public function testTenantConnection()
    {
        /* @var \Hyn\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('Hyn\MultiTenant\Contracts\HostnameRepositoryContract');
        /* @var \Hyn\MultiTenant\Models\Hostname|null $website */
        $hostname = $this->hostname->findByHostname('system.testing');

        /** @var \Hyn\MultiTenant\Tenant\DatabaseConnection $connection */
        $connection = $hostname->website->database;

        $connection->setCurrent();
        $this->assertTrue($connection->isCurrent());
        $this->assertEquals($connection->name, $connection->getCurrent());
        $this->assertTrue($connection->get() instanceof Connection);
    }

    /**
     * @depends testTenantDatabaseExists
     * @covers  \Hyn\MultiTenant\Commands\Migrate\InstallCommand
     * @covers  \Hyn\MultiTenant\Commands\Migrate\MigrateCommand
     * @covers  \TestTenantMigration
     */
    public function testTenantMigrationRuns()
    {
        $this->assertEquals(0, $this->artisan('migrate', [
            '--tenant' => 'all',
            '--path'   => '../../../tests/database/migrations/',
            '--force'  => true,
        ]));
    }

    /**
     * @depends testTenantMigrationRuns
     * @covers  \Hyn\MultiTenant\Commands\Migrate\MigrateCommand
     */
    public function testTenantMigratedTableExists()
    {
        /* @var \Hyn\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('Hyn\MultiTenant\Contracts\HostnameRepositoryContract');
        /* @var \Hyn\MultiTenant\Models\Hostname|null $website */
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
     * @covers  \Hyn\MultiTenant\Commands\Migrate\MigrateCommand
     * @covers  \Hyn\MultiTenant\Tenant\DatabaseConnection::setCurrent
     */
    public function testTenantMigrationEntryExists()
    {
        /* @var \Hyn\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('Hyn\MultiTenant\Contracts\HostnameRepositoryContract');
        /* @var \Hyn\MultiTenant\Models\Hostname|null $website */
        $hostname = $this->hostname->findByHostname('system.testing');

        if (! $hostname) {
            throw new \Exception('Unit test hostname not found');
        }

        $hostname->website->database->setCurrent();

        foreach (File::allFiles(__DIR__.'/database/migrations') as $file) {
            $fileBaseName = $file->getBaseName('.'.$file->getExtension());
            $this->seeInDatabase('migrations', ['migration' => $fileBaseName], $hostname->website->database->name);
        }
    }

    /**
     * @depends testTenantExistence
     * @covers  \Hyn\MultiTenant\Middleware\HostnameMiddleware
     * @note    we need a webserver to handle this
     */
    public function testMiddleware()
    {
        //
//        /** @var \Hyn\MultiTenant\Contracts\HostnameRepositoryContract website */
//        $this->hostname = $this->app->make('Hyn\MultiTenant\Contracts\HostnameRepositoryContract');
//        /** @var \Hyn\MultiTenant\Models\Hostname|null $website */
//        $hostname = $this->hostname->findByHostname('system.testing');
//
//        // test for unregistered hostname
//        $this->visit('http://tenant.testing/')
//            ->seeStatusCode(200)
//            ->seeJson([
//            'hostname' => null
//        ]);
//
//        // test for registered hostname
//        $this->visit('http://system.testing/')
//            ->seeStatusCode(200)
//            ->seeJson([
//            'hostname' => $hostname
//        ]);
    }
}
