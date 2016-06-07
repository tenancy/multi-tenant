<?php

namespace Hyn\Framework;

use Config;
use Hyn\Framework\Validation\ExtendedValidation;
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
        /*
         * Set configuration variables
         */
        $this->mergeConfigFrom(__DIR__.'/../../config/hyn.php', 'hyn');

        /*
         * register additional service providers if they exist
         */
        $packages = Config::get('hyn.packages', []);
        if (empty($packages)) {
            throw new \Exception("It seems config files are not available, hyn won't work without the configuration file");
        }

        foreach ($packages as $name => $package) {
            // register service provider for package
            if (class_exists(array_get($package, 'service-provider'))) {
                $this->app->register(array_get($package, 'service-provider'));
            }
            // set global state
            $this->app->bind("hyn.package.{$name}", function () use ($package) {
                return class_exists(array_get($package, 'service-provider')) ? $package : false;
            });
        }

        $this->app->validator->resolver(function ($translator, $data, $rules, $messages) {
            return new ExtendedValidation($translator, $data, $rules, $messages);
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
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
