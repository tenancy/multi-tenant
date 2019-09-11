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

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Config\Repository;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\ServiceProvider;

class RouteProvider extends ServiceProvider
{
    public function boot()
    {
        /** @var Repository $config */
        $config = $this->app->make(Repository::class);
        $path = $config->get('tenancy.routes.path');

        /** @var Router $router */
        $router = $this->app->make(Router::class);

        /** @var UrlGenerator $url */
        $url = $this->app->make('url');

        if ($path) {
            /** @var Hostname $hostname */
            $hostname = $this->app->make(CurrentHostname::class);

            if ($hostname && file_exists($path)) {
                $this->app->booted(function () use ($config, $router, $path, $url) {
                    if ($config->get('tenancy.routes.replace-global')) {
                        $router->setRoutes(new RouteCollection());
                    }

                    $router->middleware([])->group($path);

                    $router->getRoutes()->refreshNameLookups();

                    $url->setRoutes($router->getRoutes());
                });
            }
        }
    }
}
