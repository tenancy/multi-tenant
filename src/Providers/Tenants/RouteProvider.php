<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Models\Hostname;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;

class RouteProvider extends RouteServiceProvider
{
    public function boot()
    {
        $this->loadRoutes();
    }

    public function map(Repository $config, Router $router)
    {
        $path = $config->get('tenancy.routes.path');

        if ($path && $config->get('tenancy.hostname.auto-identification')) {
            /** @var Hostname $hostname */
            $hostname = $this->app->make(CurrentHostname::class);

            if ($hostname && file_exists($path)) {

                if ($config->get('tenancy.routes.replace-global')) {
                    $router->setRoutes(new RouteCollection());
                }

                $router->middleware([])->group($path);
            }
        }
    }
}
