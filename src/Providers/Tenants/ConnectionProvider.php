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
use Hyn\Tenancy\Database\Console;
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
        $this->app->singleton(Console\MigrateCommand::class, function (Application $app) {
            return new Console\MigrateCommand($app->make('migrator'));
        });
        $this->app->singleton(Console\RollbackCommand::class, function (Application $app) {
            return new Console\RollbackCommand($app->make('migrator'));
        });
        $this->app->singleton(Console\ResetCommand::class, function (Application $app) {
            return new Console\ResetCommand($app->make('migrator'));
        });
        $this->app->singleton(Console\RefreshCommand::class, function (Application $app) {
            return new Console\RefreshCommand($app->make('migrator'));
        });

        $this->commands([
            Console\MigrateCommand::class,
            Console\RollbackCommand::class,
            Console\ResetCommand::class,
            Console\RefreshCommand::class
        ]);
    }
}
