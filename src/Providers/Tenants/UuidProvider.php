<?php

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Contracts\Website\UuidGenerator;
use Hyn\Tenancy\Exceptions\GeneratorInvalidException;
use Illuminate\Support\ServiceProvider;

class UuidProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UuidGenerator::class, function ($app) {
            $generator = $app['config']->get('tenancy.website.random-id-generator');

            if (class_exists($generator)) {
                return $app->make($generator);
            }

            throw new GeneratorInvalidException($generator);
        });
    }

    public function provides()
    {
        return [
            UuidGenerator::class
        ];
    }
}
