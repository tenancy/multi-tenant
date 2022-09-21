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

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Database\Console;
use Hyn\Tenancy\Database\Resolver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Hyn\Tenancy\Database\Console\Migrations\FreshCommand;
use Hyn\Tenancy\Database\Console\Migrations\MigrateCommand;
use Hyn\Tenancy\Database\Console\Migrations\RollbackCommand;
use Hyn\Tenancy\Database\Console\Migrations\ResetCommand;
use Hyn\Tenancy\Database\Console\Migrations\RefreshCommand;
use Hyn\Tenancy\Database\Console\Seeds\SeedCommand;

class ConnectionProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Connection::class);
        $this->registerMigrationCommands();

        $this->overrideConnectionResolvers();
    }

    /**
     * Register the "migrate" migration command.
     *
     * @return void
     */
    protected function registerMigrationCommands()
    {
        $this->app->singleton(\Illuminate\Database\Console\Migrations\FreshCommand::class, function (Application $app) {
            return new FreshCommand();
        });
        $this->app->singleton(\Illuminate\Database\Console\Migrations\MigrateCommand::class, function (Application $app) {
            return new MigrateCommand();
        });
        $this->app->singleton(\Illuminate\Database\Console\Migrations\RollbackCommand::class, function (Application $app) {
            return new RollbackCommand();
        });
        $this->app->singleton(\Illuminate\Database\Console\Migrations\ResetCommand::class, function (Application $app) {
            return new ResetCommand();
        });
        $this->app->singleton(\Illuminate\Database\Console\Migrations\RefreshCommand::class, function (Application $app) {
            return new RefreshCommand();
        });
        $this->app->singleton(\Illuminate\Database\Console\Seeds\SeedCommand::class, function (Application $app) {
            return new SeedCommand($app['db']);
        });

        $this->commands([
            FreshCommand::class,
            MigrateCommand::class,
            RollbackCommand::class,
            ResetCommand::class,
            RefreshCommand::class,
            SeedCommand::class
        ]);
    }

    public function overrideConnectionResolvers()
    {
        foreach (['system', 'tenant'] as $type) {
            $models = config("tenancy.db.force-$type-connection-of-models", []);

            if (count($models)) {
                $resolver = new Resolver(
                    $this->app->make(Connection::class)->{$type . 'Name'}(),
                    $this->app['db']
                );

                foreach ($models as $class) {
                    if (class_exists($class)) {
                        forward_static_call([$class, 'setConnectionResolver'], $resolver);
                    }
                }
            }
        }
    }
}
