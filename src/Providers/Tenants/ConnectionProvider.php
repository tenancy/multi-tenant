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

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Database\Console\MigrateCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class ConnectionProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Connection::class);
        $this->registerMigrateCommand();
    }

    /**
     * Register the "migrate" migration command.
     *
     * @return void
     */
    protected function registerMigrateCommand()
    {
        $this->app->singleton('tenancy.command.migrate', function (Application $app) {
            return new MigrateCommand($app->make('migrator'));
        });

        $this->commands('tenancy.command.migrate');
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            Connection::class,
            'tenancy.command.migrate',
        ];
    }
}
