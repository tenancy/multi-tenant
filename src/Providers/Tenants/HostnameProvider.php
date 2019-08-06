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
use Hyn\Tenancy\Contracts\Tenant;
use Hyn\Tenancy\Environment;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

class HostnameProvider extends ServiceProvider implements DeferrableProvider
{
    public function provides()
    {
        return [
            CurrentHostname::class,
            Tenant::class,
        ];
    }

    public function boot(Application $app)
    {
        $app->make(Environment::class);
    }
}
