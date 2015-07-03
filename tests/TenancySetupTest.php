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
    public function testTenantExistance()
    {
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
            $tenantRepository = $this->app->make('HynMe\MultiTenant\Contracts\TenantRepositoryContract');
            $tenantRepository->forceDeleteByName('example');
        }

        parent::tearDown();
    }
}