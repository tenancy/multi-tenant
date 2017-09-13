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
        $this->app->singleton(Console\Migrations\MigrateCommand::class, function (Application $app) {
            return new Console\Migrations\MigrateCommand($app->make('migrator'));
        });
        $this->app->singleton(Console\Migrations\RollbackCommand::class, function (Application $app) {
            return new Console\Migrations\RollbackCommand($app->make('migrator'));
        });
        $this->app->singleton(Console\Migrations\ResetCommand::class, function (Application $app) {
            return new Console\Migrations\ResetCommand($app->make('migrator'));
        });
        $this->app->singleton(Console\Migrations\RefreshCommand::class, function (Application $app) {
            return new Console\Migrations\RefreshCommand($app->make('migrator'));
        });
        $this->app->singleton(Console\Seeds\SeedCommand::class, function (Application $app) {
            return new Console\Seeds\SeedCommand($app['db']);
        });

        $this->commands([
            Console\Migrations\MigrateCommand::class,
            Console\Migrations\RollbackCommand::class,
            Console\Migrations\ResetCommand::class,
            Console\Migrations\RefreshCommand::class,
            Console\Seeds\SeedCommand::class
        ]);
    }
}
