<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 *
 */

namespace Hyn\Tenancy\Providers;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class WebserverProvider extends ServiceProvider
{
    public function boot()
    {
        $this->integrateServices();
    }

    public function register()
    {
        // Sets file access as wide as possible, ignoring server masks.
        umask(0);
        $this->registerConfiguration();
        $this->registerGeneratorViews();

        $this->app->register(Webserver\EventProvider::class);
    }

    protected function registerConfiguration()
    {
        $this->publishes([
            __DIR__ . '/../../assets/configs/webserver.php' => config_path('webserver.php')
        ], 'tenancy');
    }

    protected function registerGeneratorViews()
    {
        $this->loadViewsFrom(
            __DIR__ . '/../../assets/generators',
            'tenancy.generator'
        );
    }

    protected function integrateServices()
    {
        collect(config('webserver', []))
            ->filter(function ($service) {
                return Arr::get($service, 'enabled');
            })
            ->each(function ($config, $service) {
                $generator = Arr::get($config, 'generator');

                if ($generator && class_exists($generator)) {
                    $this->app->singleton("service.$service", $generator);
                }
            });
    }
}
