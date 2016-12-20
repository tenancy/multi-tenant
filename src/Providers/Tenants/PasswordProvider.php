<?php

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Contracts\Database\PasswordGenerator;
use Hyn\Tenancy\Exceptions\GeneratorInvalidException;
use Illuminate\Support\ServiceProvider;

class PasswordProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(PasswordGenerator::class, function ($app) {
            $generator = $app['config']->get('tenancy.website.password-generator');

            if (class_exists($generator)) {
                return new $generator;
            }

            throw new GeneratorInvalidException($generator);
        });
    }

    public function provides()
    {
        return [
            PasswordGenerator::class
        ];
    }
}
