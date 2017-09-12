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

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Database\Console\MigrateCommand;
use Hyn\Tenancy\Database\Console\RollbackCommand;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;

class ConnectionProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Connection::class);
        $this->registerMigrationCommands();
    }

    /**
     * Register the "migrate" migration command.
     *
     * @return void
     */
    protected function registerMigrationCommands()
    {
        $this->app->singleton(MigrateCommand::class, function (Application $app) {
            return new MigrateCommand($app->make('migrator'));
        });
        $this->app->singleton(RollbackCommand::class, function (Application $app) {
            return new RollbackCommand($app->make('migrator'));
        });

        $this->commands([
            MigrateCommand::class,
            RollbackCommand::class
        ]);
    }
}
