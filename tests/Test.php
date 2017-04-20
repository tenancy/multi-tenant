<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Providers\TenancyProvider;
use Hyn\Tenancy\Providers\WebserverProvider;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase;
use Throwable;

/**
 * Class Test
 * @package Hyn\Tenancy\Tests
 */
class Test extends TestCase
{
    /**
     * @var Hostname
     */
    protected $hostname;

    /**
     * @var Hostname
     */
    protected $tenant;
    /**
     * Service providers to load during this test.
     *
     * @var array
     */
    protected $loadProviders = [
        TenancyProvider::class,
        WebserverProvider::class
    ];

    /**
     * Creates the application.
     *
     * Needs to be implemented by subclasses.
     *
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    public function createApplication()
    {
        $appPaths = [];
        if (getenv('CI_PROJECT_DIR')) {
            $appPaths[] = realpath(getenv('CI_PROJECT_DIR') . '/vendor/laravel/laravel');
        }
        $appPaths[] = realpath(__DIR__ . '/..');
        $appPaths[] = realpath(__DIR__ . '/../vendor/laravel/laravel');

        foreach ($appPaths as $path) {
            $path = "$path/bootstrap/app.php";
            if (file_exists($path)) {
                /** @var Application $app */
                $app = require $path;
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

        touch(database_path('database.sqlite'));

        $this->duringSetUp($app);

        return $app;
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
     * {@inheritdoc}
     */
    protected function onNotSuccessfulTest(Throwable $t)
    {
        static::cleanupTestingDatabase();
        parent::onNotSuccessfulTest($t);
    }

    protected static function cleanupTestingDatabase()
    {
        if (file_exists(database_path('database.sqlite'))) {
            unlink(database_path('database.sqlite'));
        }
    }

    protected function loadHostnames()
    {
        $this->hostname = Hostname::where('fqdn', 'local.testing')->firstOrFail();
        $this->tenant = Hostname::where('fqdn', 'tenant.testing')->firstOrFail();
    }

    protected function setUpHostnames()
    {
        Hostname::unguard();

        $hostname = new Hostname([
            'fqdn' => 'local.testing',
            'redirect_to' => null,
            'force_https' => false,
        ]);

        $this->hostname = $hostname;

        $tenant = new Hostname([
            'fqdn' => 'tenant.testing',
            'redirect_to' => null,
            'force_https' => false
        ]);

        $this->tenant = $tenant;

        Hostname::reguard();
    }
}
