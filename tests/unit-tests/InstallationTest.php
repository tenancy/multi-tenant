<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Providers\TenancyProvider;
use Hyn\Tenancy\Providers\Tenants as Providers;
use Hyn\Tenancy\Providers\WebserverProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Arr;

class InstallationTest extends Test
{
    /**
     * @var Hostname
     */
    protected $hostname;

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
            '-n' => 1
        ]);

        $this->assertEquals(0, $code, 'Publishing vendor files failed');

        $this->assertFileExists(config_path('tenancy.php'));
    }

    /**
     * @test
     */
    public function migration_table_created()
    {
        $code = $this->artisan('migrate:install', [
            '-n' => 1
        ]);

        $this->assertEquals(0, $code, 'Migration table creation failed');
    }

    /**
     * @test
     * @depends migration_table_created
     * @depends publishes_vendor_files
     */
    public function install_command_works()
    {
        $code = $this->artisan('tenancy:install', [
            '-n' => 1
        ]);

        $this->assertEquals(0, $code, 'Installation didn\'t work out');
    }

    /**
     * @test
     * @depends install_command_works
     */
    public function migration_succeeded()
    {
        Hostname::query()->first();
    }

    /**
     * @test
     * @depends migration_succeeded
     */
    public function hostname_identification_returns_default()
    {
        $this->assertEquals(
            $this->app->make(HostnameRepository::class)->getDefault(),
            $this->app->make(CurrentHostname::class)
        );
    }

    /**
     * @test
     * @depends migration_succeeded
     */
    public function hostname_identification_returns_env()
    {
        putenv('TENANCY_CURRENT_HOSTNAME=local.testing');

        $this->assertEquals(
            $this->hostname,
            $this->app->make(CurrentHostname::class)
        );
    }

    /**
     * @param Application $app
     */
    protected function duringSetUp(Application $app)
    {
        Hostname::unguard();

        $hostname = new Hostname([
            'fqdn' => 'local.testing',
            'redirect_to' => null,
            'force_https' => false,
        ]);

        $this->hostname = $hostname;
    }
}
