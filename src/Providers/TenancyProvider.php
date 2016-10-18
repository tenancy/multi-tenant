<?php

namespace Hyn\Tenancy\Providers;

use Hyn\Tenancy\Contracts\Database\PasswordGenerator;
use Hyn\Tenancy\Contracts\Website\UuidGenerator;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Exceptions\UuidGeneratorInvalidException;
use Hyn\Tenancy\Generators\Uuid\SimpleStringGenerator;
use Hyn\Tenancy\Listeners\AffectServicesListener;
use Hyn\Tenancy\Providers\Tenants\BusProvider;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class TenancyProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerSupportingProviders();
        $this->registerConfiguration();
        $this->registerListeners();
        $this->registerBinds();
    }

    public function boot()
    {
        // Immediately instantiate the object to work the magic.
        $environment = $this->app->make(Environment::class);
        // Now register it into ioc to make it globally available.
        $this->app->singleton(Environment::class, function() use ($environment) {
            return $environment;
        });
    }

    protected function registerSupportingProviders()
    {
        $this->app->register(BusProvider::class);
    }

    protected function registerConfiguration()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../assets/configs/tenancy.php',
            'tenancy'
        );
        $this->publishes([
            __DIR__ . '/../../assets/configs/tenancy.php' => config_path('tenancy.php')
        ], 'tenancy');
    }

    protected function registerListeners()
    {
        $this->app->singleton(Connection::class);
        AffectServicesListener::registerService($this->app->make(Connection::class));

        // ..

        $this->app->make(Dispatcher::class)->subscribe(AffectServicesListener::class);
    }

    protected function registerBinds()
    {
        $this->app->bind(UuidGenerator::class, function($app) {
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

        $this->app->bind(PasswordGenerator::class, function($app) {
            $generator = $app['config']->get('tenancy.website.password-generator');

            if (class_exists($generator)) {
                return new $generator;
            }

            throw new GeneratorInvalidException($generator);
        });
    }
}
