<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Tenancy\Providers\TenancyProvider;
use Hyn\Tenancy\Providers\WebserverProvider;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase;

class Test extends TestCase
{

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
            $appPaths[] = realpath(getenv('CI_PROJECT_DIR') . '/vendor/laravel/laravel/bootstrap/app.php');
        }
        $appPaths[] = realpath('./bootstrap/app.php');

        foreach ($appPaths as $path) {
            if (file_exists($path)) {
                /** @var \Illuminate\Foundation\Application $app */
                $app = require $path;
                break;
            }
        }

        $app->make(Kernel::class)->bootstrap();

        if (!$app->register(TenancyProvider::class)) {
            throw new \RuntimeException("Failed to register Tenancy service provider");
        }

        if (!$app->register(WebserverProvider::class)) {
            throw new \RuntimeException("Failed to register Tenancy webserver service provider");
        }

        return $app;
    }
}
