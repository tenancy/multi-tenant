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

namespace Hyn\Tenancy\Providers\Webserver;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class FilesystemProvider extends ServiceProvider
{
    public function register()
    {
        $this->addDisks();
    }

    protected function addDisks()
    {
        collect(config('webserver', []))
            ->filter(function (array $config) {
                return Arr::has($config, 'generator');
            })
            ->keys()
            ->each(function (string $service) {
                $this->app['config']->set("filesystems.disks.tenancy-webserver-$service", [
                    'driver' => 'local',
                    'root' => storage_path("app/tenancy/webserver/$service")
                ]);
            });
    }
}
