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
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Queue;
use Schema;

class Test extends TestCase
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

    public function createApplication()
    {
        $appPaths = [];
        if (getenv('CI_PROJECT_DIR')) {
            $appPaths[] = realpath(getenv('CI_PROJECT_DIR') . '/vendor/laravel/laravel');
        }
        $appPaths[] = realpath(__DIR__ . '/..');
        $appPaths[] = realpath(__DIR__ . '/../vendor/laravel/laravel');

        $app = false;

        foreach ($appPaths as $path) {
            $boot = "$path/bootstrap/app.php";
            if (file_exists($boot)) {
                /** @var Application $app */
                $app = require $boot;

                $this->pathIdentified($path);

                break;
            }
        }

        if (!$app) {
            throw new \RuntimeException("No bootstrap file found, make sure laravel/laravel is installed");
        }

        $app->make(Kernel::class)->bootstrap();

        foreach ($this->loadProviders as $provider) {
            if (!$app->register($provider)) {
                throw new \RuntimeException("Failed registering $provider");
            }
        }

        $this->setSchemaLength();

        $this->identifyBuild();

        $this->beforeSetUp($app);

        $this->setUpTenancy();

        return $app;
    }

    protected function setUp() : void
    {
        parent::setUp();

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

    protected function pathIdentified(string $path)
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
