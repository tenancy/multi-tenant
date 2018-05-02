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
use Hyn\Tenancy\Environment;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class HostnameProvider extends ServiceProvider
{
    public $defer = true;

    public function provides()
    {
        return [CurrentHostname::class];
    }

    public function boot(Application $app)
    {
        $app->make(Environment::class);
    }
}
