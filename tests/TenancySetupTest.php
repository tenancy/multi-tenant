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

    public function tearDown()
    {
        /** @var \HynMe\MultiTenant\Contracts\TenantRepositoryContract $tenantRepository */
        $tenantRepository = $this->app->make('HynMe\MultiTenant\Contracts\TenantRepositoryContract');
        $tenantRepository->forceDeleteByName('example');

        parent::tearDown();
    }
}