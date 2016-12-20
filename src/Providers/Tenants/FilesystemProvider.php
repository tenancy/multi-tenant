<?php

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Contracts\Website\Filesystem;
use Illuminate\Support\ServiceProvider;

class FilesystemProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(Filesystem::class, function ($app) {
            /** @var \Illuminate\Filesystem\FilesystemManager $manager */
            $manager = $app->make('filesystem');

            return $manager->disk($app['config']->get('tenancy.website.disk'));
        });
    }
}
