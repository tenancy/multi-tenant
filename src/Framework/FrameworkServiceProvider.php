<?php

namespace Hyn\Framework;

use Hyn\Framework\Validation\ExtendedValidation;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class FrameworkServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/hyn.php', 'hyn');

        $this->app->validator->resolver(function ($translator, $data, $rules, $messages) {
            return new ExtendedValidation($translator, $data, $rules, $messages);
        });
    }

    /**
     * Register the service provider.
     *
     * @throws \Exception
     */
    public function register()
    {
        $config = require __DIR__ . '/../../config/hyn.php';
        $packages = Arr::get($config, 'packages', []);

        if (empty($packages)) {
            throw new \Exception("It seems config files are not available, hyn won't work without the configuration file");
        }

        foreach ($packages as $name => $package) {
            // register service provider for package
            if (class_exists(Arr::get($package, 'service-provider'))) {
                $this->app->register(Arr::get($package, 'service-provider'));
            }
            // set global state
            $this->app->bind("hyn.package.$name", function () use ($package) {
                return class_exists(Arr::get($package, 'service-provider')) ? $package : false;
            });
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
