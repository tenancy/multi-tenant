<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Providers;

use Hyn\Tenancy\Listeners\Servant;
use Illuminate\Support\ServiceProvider;

class WebserverProvider extends ServiceProvider
{
    public function register()
    {
        // Sets file access as wide as possible, ignoring server masks.
        umask(0);
        $this->registerConfiguration();
        $this->registerGeneratorViews();

        $this->app->register(Webserver\FilesystemProvider::class);
        $this->app->register(Webserver\EventProvider::class);

        $this->app->singleton(Servant::class);
    }

    protected function registerConfiguration()
    {
        $this->publishes([
            __DIR__ . '/../../assets/configs/webserver.php' => config_path('webserver.php')
        ], 'tenancy');
        $this->mergeConfigFrom(
            __DIR__ . '/../../assets/configs/webserver.php',
            'webserver'
        );
    }

    protected function registerGeneratorViews()
    {
        $this->loadViewsFrom(
            __DIR__ . '/../../assets/generators',
            'tenancy.generators'
        );
    }
}
