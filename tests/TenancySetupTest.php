<?php namespace HynMe\MultiTenant\Tests;

use Artisan;
use HynMe\Framework\Testing\TestCase;

class TenancySetupTest extends TestCase
{
    public function testCommand()
    {
        Artisan::call('multi-tenant:setup', [
            '--tenant' => 'example',
            '--email' => 'info@example.org',
            '--hostname' => 'example.org'
        ]);
    }

    /**
     * @depends testCommand
     */
    public function testTenantExistence()
    {
        /** @var \HynMe\MultiTenant\Contracts\HostnameRepositoryContract hostname */
        $this->hostname = $this->app->make('HynMe\MultiTenant\Contracts\HostnameRepositoryContract');
        /** @var \HynMe\MultiTenant\Models\Hostname|null $hostname */
        $hostname = $this->hostname->findByHostname('example.org');

        $this->assertNotNull($hostname);

        $this->assertEquals($hostname->hostname, 'example.org');

        $this->assertNotNull($hostname->tenant);

        $this->assertEquals($hostname->tenant->present()->name, 'example');
    }

    public function tearDown()
    {
        if($this->app) {
            /** @var \HynMe\MultiTenant\Contracts\TenantRepositoryContract $tenantRepository */
            $this->tenant = $this->app->make('HynMe\MultiTenant\Contracts\TenantRepositoryContract');
            $this->tenant->forceDeleteByName('example');
        }

        parent::tearDown();
    }
}