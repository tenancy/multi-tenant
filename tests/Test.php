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

use Hyn\Tenancy\Providers\TenancyProvider;
use Hyn\Tenancy\Providers\WebserverProvider;
use Hyn\Tenancy\Tests\Traits\InteractsWithBuilds;
use Hyn\Tenancy\Tests\Traits\InteractsWithMigrations;
use Hyn\Tenancy\Tests\Traits\InteractsWithTenancy;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * The suite runs on the application skeleton Orchestra Testbench ships, not on
 * a copy of laravel/laravel located at runtime. Testbench is what every other
 * Laravel package uses, it absorbs the skeleton changes each framework release
 * brings, and it is the only way to reach a booted HTTP kernel from a test.
 */
class Test extends BaseTestCase
{
    use InteractsWithBuilds,
        InteractsWithMigrations,
        InteractsWithTenancy;

    /**
     * Service providers to load during this test.
     *
     * @var array
     */
    protected $loadProviders = [
        TenancyProvider::class,
        WebserverProvider::class
    ];

    public $mockConsoleOutput = false;

    /**
     * @param Application $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return $this->loadProviders;
    }

    /**
     * Runs before the providers are registered, which is the only moment a test
     * can still put files where a provider will look for them during boot.
     *
     * @param Application $app
     */
    protected function defineEnvironment($app)
    {
        $this->prepareSkeleton($app->basePath());
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->setSchemaLength();

        $this->identifyBuild();

        $this->beforeSetUp($this->app);

        $this->setUpTenancy();

        $this->migrateSystem();

        $this->duringSetUp($this->app);
    }

    protected function setSchemaLength()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Allows implementation in a test.
     *
     * @param Application $app
     */
    protected function beforeSetUp(Application $app)
    {
        // ..
    }

    /**
     * Allows implementation in a test.
     *
     * @param Application $app
     */
    protected function duringSetUp(Application $app)
    {
        // ..
    }

    /**
     * Allows implementation in a test.
     *
     * @param string $path The base path of the application under test.
     */
    protected function prepareSkeleton(string $path)
    {
        // ..
    }

    protected function tearDown() : void
    {
        Queue::createPayloadUsing(null);
        $this->cleanupTenancy();
        parent::tearDown();
    }
}
