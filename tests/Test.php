<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Providers\TenancyProvider;
use Hyn\Tenancy\Providers\WebserverProvider;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase;

class Test extends TestCase
{

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
                /** @var \Illuminate\Foundation\Application $app */
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

        return $app;
    }

    /**
     * {@inheritdoc}
     */
    protected function onNotSuccessfulTest($e)
    {
        static::cleanupTestingDatabase();
        parent::onNotSuccessfulTest($e);
    }

    /**
     * {@inheritdoc}
     */
    public static function tearDownAfterClass()
    {
        static::cleanupTestingDatabase();
        parent::tearDownAfterClass();
    }

    protected static function cleanupTestingDatabase()
    {
        if (file_exists(database_path('database.sqlite'))) {
            unlink(database_path('database.sqlite'));
        }
    }
}
