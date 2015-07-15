<?php namespace HynMe\MultiTenant\Tests;


use File, Queue;
use HynMe\Framework\Testing\TestCase;
use HynMe\MultiTenant\MultiTenantServiceProvider;

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
        $this->assertEquals($this->artisan('multi-tenant:setup', [
            '--tenant' => 'example',
            '--hostname' => 'example.org',
            '--email' => 'info@example.org',
            '--webserver' => 'no'
        ]), 0);


    }

    /**
     * @depends testCommand
     */
    public function testTenantExistence()
    {
        /** @var \HynMe\MultiTenant\Contracts\TenantRepositoryContract tenant */
        $this->tenant = $this->app->make('HynMe\MultiTenant\Contracts\TenantRepositoryContract');
        /** @var \HynMe\MultiTenant\Models\Tenant|null $tenant */
        $tenant = $this->tenant->findByName('example');

        $this->assertNotNull($tenant, 'Tenant from command has not been created');
    }

    /**
     * @depends testTenantExistence
     */
    public function testHostnameExistence()
    {
        /** @var \HynMe\MultiTenant\Contracts\HostnameRepositoryContract hostname */
        $this->hostname = $this->app->make('HynMe\MultiTenant\Contracts\HostnameRepositoryContract');

        /** @var \HynMe\MultiTenant\Models\Hostname|null $hostname */
        $hostname = $this->hostname->findByHostname('example.org');

        $this->assertNotNull($hostname, 'Hostname from command has not been created');

    }

    /**
     * @depends testTenantExistence
     */
    public function testTenantMigrationRuns()
    {
        $this->assertEquals($this->artisan('migrate', [
            '--tenant' => 'true',
            '--path' => __DIR__ . '/database/migrations/'
        ]), 0);
    }

    /**
     * @depends testTenantMigrationRuns
     */
    public function testTenantMigrationEntryExists()
    {
        /** @var \HynMe\MultiTenant\Contracts\HostnameRepositoryContract website */
        $this->hostname = $this->app->make('HynMe\MultiTenant\Contracts\HostnameRepositoryContract');
        /** @var \HynMe\MultiTenant\Models\Hostname|null $website */
        $hostname = $this->hostname->findByHostname('example.org');

        if(!$hostname)
            throw new \Exception("Unit test hostname not found");

        $hostname->website->database->setCurrent();

        foreach(File::allFiles(__DIR__ . '/database/migrations') as $file)
        {
            $fileBaseName = $file->getBaseName('.'.$file->getExtension());
            $this->seeInDatabase('migrations', ['migration' => $fileBaseName], 'tenant');
        }
    }
}