<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Providers\TenancyProvider;
use Hyn\Tenancy\Providers\Tenants as Providers;
use Hyn\Tenancy\Providers\WebserverProvider;
use Illuminate\Support\Arr;

class FrameworkIntegrationTest extends Test
{
    /**
     * @test
     */
    public function service_providers_registered()
    {
        foreach ([
                     TenancyProvider::class,
                     WebserverProvider::class,
                     Providers\BusProvider::class,
                     Providers\ConfigurationProvider::class,
                     Providers\EventProvider::class,
                     Providers\PasswordProvider::class,
                     Providers\UuidProvider::class
                 ] as $provider) {
            $this->assertTrue(
                Arr::get($this->app->getLoadedProviders(), $provider, false),
                "$provider is not registered"
            );
        }
    }

    /**
     * @test
     */
    public function configurations_are_loaded()
    {
        $this->assertFalse(config('tenancy.website.disable-random-id'));
    }

    /**
     * @test
     */
    public function publishes_vendor_files()
    {
        $code = $this->artisan('vendor:publish', [
            '--tag' => 'tenancy',
            '--force' => 1,
            '-n' => 1
        ]);

        $this->assertEquals(0, $code, 'Publishing vendor files failed');
    }

    /**
     * @test
     * @depends publishes_vendor_files
     */
    public function runs_migrations()
    {
        $code = $this->artisan('migrate', [
            '--path' => __DIR__ . '/../../assets/migrations',
            '-n' => 1
        ]);

        $this->assertEquals(0, $code, 'Migrating system files failed');
    }
}
