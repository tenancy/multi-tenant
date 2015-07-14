<?php namespace HynMe\MultiTenant\Tests;

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
            '--path' => __DIR__ . 'database/migrations'

        ]), 0);
    }

    /**
     * @depends testTenantMigrationRuns
     */
    public function testTenantMigrationEntryExists()
    {
        /** @var \HynMe\MultiTenant\Contracts\WebsiteRepositoryContract website */
        $this->website = $this->app->make('HynMe\MultiTenant\Contracts\WebsiteRepositoryContract');
        /** @var \HynMe\MultiTenant\Models\Website|null $website */
        $website = $this->website->findByHostname('example.org');

        foreach(\File::allFiles(__DIR__ . 'database/migrations') as $file)
        {
            $this->assertGreaterThan(0, $website->database->get()->table('migrations')->where('migration', $file->getBaseName('.'.$file->getExtension()))->count());
        }
    }
}