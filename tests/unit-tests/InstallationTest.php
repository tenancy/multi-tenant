<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Contracts\Website\UuidGenerator;
use Hyn\Tenancy\Generators\Uuid\ShaGenerator;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Providers\TenancyProvider;
use Hyn\Tenancy\Providers\Tenants as Providers;
use Hyn\Tenancy\Providers\WebserverProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\QueryException;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;

class InstallationTest extends Test
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
                     Providers\ConnectionProvider::class,
                     Providers\EventProvider::class,
                     Providers\FilesystemProvider::class,
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
        $this->assertFileExists(config_path('tenancy.php'));
        $this->assertFileExists(database_path('migrations/2017_01_01_000003_tenancy_websites.php'));
    }

    /**
     * @test
     */
    public function migration_succeeded()
    {
        $works = true;

        try {
            Hostname::first();
        } catch (QueryException $e) {
            $works = false;
        }

        $this->assertTrue($works, 'Database not migrated');
    }

    /**
     * @test
     * @depends migration_succeeded
     */
    public function saves_default_hostname()
    {
        $this->setUpHostnames();

        $this->hostnames->create($this->hostname);

        $this->assertTrue($this->hostname->exists);
    }

    /**
     * @test
     * @depends saves_default_hostname
     */
    public function hostname_identification_returns_default()
    {
        $this->setUpHostnames(true);

        config(['tenancy.hostname.default' => $this->hostname->fqdn]);

        $this->assertEquals(
            $this->hostname->fqdn,
            $this->app->make(CurrentHostname::class)->fqdn
        );
    }

    /**
     * @test
     * @depends saves_default_hostname
     * @covers \Hyn\Tenancy\Contracts\Repositories\HostnameRepository::getDefault
     * @covers \Hyn\Tenancy\Repositories\HostnameRepository::getDefault
     */
    public function verify_request()
    {
        $this->setUpHostnames(true);

        config(['tenancy.hostname.default' => $this->hostname->fqdn]);

        $response = $this->get('http://localhost/default');

        $response->assertJsonFragment(['fqdn' => $this->hostname->fqdn]);
    }

    /**
     * @test
     * @covers \Hyn\Tenancy\Generators\Uuid\ShaGenerator
     */
    public function verify_uuid_generator()
    {
        $this->setUpWebsites();

        /** @var ShaGenerator $generator */
        $generator = $this->app->make(UuidGenerator::class);

        $this->assertInstanceOf(ShaGenerator::class, $generator);

        $this->assertTrue(is_string($generator->generate($this->website)));
    }

    /**
     * @param Application $app
     */
    protected function duringSetUp(Application $app)
    {
        $router = $app->make(Router::class);

        $router->get('default', function () {
            return app(CurrentHostname::class)->toJson();
        });
    }
}
