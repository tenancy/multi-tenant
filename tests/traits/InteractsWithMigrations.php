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

namespace Hyn\Tenancy\Tests\Traits;

use Hyn\Tenancy\Providers\TenancyProvider;
use Hyn\Tenancy\Providers\Tenants\ConfigurationProvider;
use Illuminate\Database\Eloquent\Collection;
use SampleSeeder;
use Mockery;
use Hyn\Tenancy\Database\Connection;
use Illuminate\Contracts\Console\Kernel;

trait InteractsWithMigrations
{
    protected function migrateSystem()
    {
        $this->connection->system()->getSchemaBuilder()->dropAllTables();

        // publish configuration files
        $this->artisan('vendor:publish', [
            '--provider' => ConfigurationProvider::class,
            '--no-interaction' => 1
        ]);

        // publish migrations
        $this->artisan('vendor:publish', [
            '--provider' => TenancyProvider::class,
            '--no-interaction' => 1
        ]);

        // refresh database
        $this->artisan('migrate:fresh', [
            '--no-interaction' => 1,
            '--force' => 1
        ]);
    }

    /**
     * @param string        $command
     * @param callable|null $callback
     * @param callable|null $hook
     * @param array         $commandOptions
     */
    protected function migrateAndTest(string $command, callable $callback = null, callable $hook = null, array $commandOptions = [])
    {
        $code = $this->artisan("tenancy:$command", array_merge([
            '--realpath' => true,
            '--path' => __DIR__ . '/../migrations',
            '--no-interaction' => 1,
            '--force' => true
        ], $commandOptions));

        $this->assertEquals(0, $code, "tenancy:$command didn't work out");

        if ($hook) {
            $hook();
        }

        if ($callback) {
            $this->websites->query()->chunk(10, function (Collection $websites) use ($callback) {
                $websites->each($callback);
            });
        }
    }

    /**
     * @param callable|null $callback
     * @param callable|null $hook
     */
    protected function seedAndTest(callable $callback = null, callable $hook = null)
    {
        $code = $this->artisan("tenancy:db:seed", [
            '--class' => SampleSeeder::class,
            '--no-interaction' => 1,
            '--force' => true
        ]);

        $this->assertEquals(0, $code, "tenancy:db:seed didn't work out");

        if ($hook) {
            $hook();
        }

        if ($callback) {
            $this->websites->query()->chunk(10, function (Collection $websites) use ($callback) {
                $websites->each($callback);
            });
        }
    }

    protected function swapConnectionWithSpy()
    {
        $spy = Mockery::spy($this->app[Connection::class]);

        $this->app[Connection::class] = $spy;

        return $spy;
    }

    protected function reloadArtisanCommand($command)
    {
        $this->app->forgetInstance($command);

        $kernel = $this->app[Kernel::class];
        $kernel->registerCommand($this->app[$command]);
    }
}
