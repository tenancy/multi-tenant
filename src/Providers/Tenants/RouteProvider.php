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
use Illuminate\Support\ServiceProvider;

class RouteProvider extends ServiceProvider
{
    public function register()
    {
        if ($this->app['config']->get('tenancy.hostname.auto-identification')) {
            /** @var Hostname $hostname */
            $hostname = $this->app->make(CurrentHostname::class);

            if ($hostname && file_exists(base_path('routes/tenants.php'))) {
                $this->app['router']->domain($hostname->fqdn)
                    ->group(base_path('routes/tenants.php'));
            }
        }
    }
}
