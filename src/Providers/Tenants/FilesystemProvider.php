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

use Hyn\Tenancy\Abstracts\AbstractTenantDirectoryListener;
use Hyn\Tenancy\Website\Directory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class FilesystemProvider extends ServiceProvider
{
    public function register()
    {
        $this->addDisks();

        $this->app->singleton('tenancy.disk', function ($app) {
            /** @var \Illuminate\Filesystem\FilesystemManager $manager */
            $manager = $app->make('filesystem');

            return $manager->disk($app['config']->get('tenancy.website.disk') ?: 'tenancy-default');
        });

        $this->app->when(Directory::class)
            ->needs(Filesystem::class)
            ->give('tenancy.disk');

        $this->app->when(AbstractTenantDirectoryListener::class)
            ->needs(Filesystem::class)
            ->give('tenancy.disk');

        $this->app->bind(Directory::class);
    }

    protected function addDisks()
    {
        $this->app['config']->set('filesystems.disks.tenancy-default', [
            'driver' => 'local',
            'root' => storage_path('app/tenancy/tenants')
        ]);
    }
}
