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

use Illuminate\Support\ServiceProvider;

class ConfigurationProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../assets/configs/tenancy.php',
            'tenancy'
        );
        $this->publishes([
            __DIR__ . '/../../../assets/configs/tenancy.php' => config_path('tenancy.php')
        ], 'tenancy');
    }
}
