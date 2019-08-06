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

use Hyn\Tenancy\Commands\RunCommand;
use Hyn\Tenancy\Commands\RecreateCommand;
use Hyn\Tenancy\Commands\UpdateKeyCommand;
use Hyn\Tenancy\Contracts;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Listeners\Database\FlushHostnameCache;
use Hyn\Tenancy\Repositories;
use Hyn\Tenancy\Providers\Tenants as Providers;
use Hyn\Tenancy\Contracts\Website as WebsiteContract;
use Hyn\Tenancy\Contracts\Hostname as HostnameContract;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class TenancyProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../assets/configs/tenancy.php',
            'tenancy'
        );

        $this->publishes(
            [__DIR__ . '/../../assets/migrations' => database_path('migrations')],
            'tenancy'
        );


        $this->app->booted(function ($app) {
            $app->singleton(Environment::class, function ($app) {
                return new Environment($app);
            });
        });
        $this->app->singleton(Contracts\Repositories\HostnameRepository::class, Repositories\HostnameRepository::class);
        $this->app->singleton(Contracts\Repositories\WebsiteRepository::class, Repositories\WebsiteRepository::class);

        $this->registerModels();

        $this->registerProviders();

        $this->registerMiddleware();
    }

    public function boot()
    {
        $this->bootCommands();
    }

    protected function registerModels()
    {
        $config = $this->app['config']['tenancy.models'];

        $this->app->bind(HostnameContract::class, $config['hostname']);
        $this->app->bind(WebsiteContract::class, $config['website']);

        forward_static_call([$config['hostname'], 'observe'], FlushHostnameCache::class);
    }

    protected function registerProviders()
    {
        $this->app->register(Providers\ConfigurationProvider::class);
        $this->app->register(Providers\PasswordProvider::class);
        $this->app->register(Providers\ConnectionProvider::class);
        $this->app->register(Providers\UuidProvider::class);
        $this->app->register(Providers\BusProvider::class);
        $this->app->register(Providers\FilesystemProvider::class);
        $this->app->register(Providers\HostnameProvider::class);
        $this->app->register(Providers\DatabaseDriverProvider::class);
        $this->app->register(Providers\QueueProvider::class);

        // Register last.
        $this->app->register(Providers\EventProvider::class);
        $this->app->register(Providers\RouteProvider::class);
    }

    protected function bootCommands()
    {
        $this->commands([
            RecreateCommand::class,
            RunCommand::class,
            UpdateKeyCommand::class,
        ]);
    }

    protected function registerMiddleware()
    {
        $middleware = $this->app['config']['tenancy.middleware'];

        /** @var Kernel|\Illuminate\Foundation\Http\Kernel $kernel */
        $kernel = $this->app->make(Kernel::class);

        foreach ($middleware as $mw) {
            $kernel->prependMiddleware($mw);
        }
    }

    public function provides()
    {
        return [
            Environment::class,
            Contracts\Repositories\HostnameRepository::class,
            Contracts\Repositories\WebsiteRepository::class,
        ];
    }
}
