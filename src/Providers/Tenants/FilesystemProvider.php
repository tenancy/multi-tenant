<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 *
 */

namespace Hyn\Tenancy\Providers\Tenants;

use Hyn\Tenancy\Generators\Filesystem\DirectoryGenerator;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\ServiceProvider;

class FilesystemProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('tenant.disk', function ($app) {
            /** @var \Illuminate\Filesystem\FilesystemManager $manager */
            $manager = $app->make('filesystem');

            return $manager->disk($app['config']->get('tenancy.website.disk'));
        });

        $this->app->when(DirectoryGenerator::class)
            ->needs(Filesystem::class)
            ->give('tenant.disk');
    }
}
