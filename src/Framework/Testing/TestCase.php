<?php

namespace Hyn\Framework\Testing;

use Config;
use Hyn\Framework\FrameworkServiceProvider;
use Illuminate\Foundation\Testing\TestCase as IlluminateTestCase;

class TestCase extends IlluminateTestCase
{
    /**
     * Creates the application.
     *
     * @param null $app_file
     * @return \Illuminate\Foundation\Application
     * @throws \Exception
     */
    public function createApplication($app_file = null)
    {
        if (empty($app_file)) {
            if (getenv('BUILD_DIR')) {
                $app_file = realpath(getenv('BUILD_DIR') . '/vendor/laravel/laravel/bootstrap/app.php');
            } else {
                $app_file = realpath('./bootstrap/app.php');
            }
        }
        /** @var \Illuminate\Foundation\Application $app */
        $app = require $app_file;

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        Config::set('database.connections.hyn', Config::get('database.connections.mysql'));

        // register framework service provider and all dependancies
        $provider = $app->register(FrameworkServiceProvider::class);

        // register testing routes
        $app['router']->any(
            '/tenant/view',
            function () use ($app) {
            return \Response::json($app->make('tenant.view'));
        });

        if (! $provider) {
            throw new \Exception('Required framework service provider not registered/booted for use during unit testing');
        }

        return $app;
    }
}
