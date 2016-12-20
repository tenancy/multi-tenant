<?php

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Contracts\Website\UuidGenerator;
use Hyn\Tenancy\Exceptions\GeneratorInvalidException;
use Hyn\Tenancy\Generators\Uuid\SimpleStringGenerator;
use Illuminate\Support\ServiceProvider;

class UuidProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UuidGenerator::class, function ($app) {
            $randomized = $app['config']->get('tenancy.website.disable-random-id', true);

            if ($randomized) {
                $generator = $app['config']->get('tenancy.website.random-id-generator');
            } else {
                $generator = SimpleStringGenerator::class;
            }

            if (class_exists($generator)) {
                return new $generator;
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
