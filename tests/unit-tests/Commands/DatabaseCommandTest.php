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

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Console\Migrations\MigrateCommand;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Tests\Traits\InteractsWithMigrations;
use Illuminate\Contracts\Foundation\Application;

class DatabaseCommandTest extends Test
{
    use InteractsWithMigrations;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
    }

    /**
     * @test
     */
    public function is_ioc_bound()
    {
        $this->assertInstanceOf(
            MigrateCommand::class,
            $this->app->make(MigrateCommand::class)
        );
    }

    /**
     * @test
     */
    public function runs_migrate_on_tenants()
    {
        $this->migrateAndTest('migrate', function (Website $website) {
            $this->connection->set($website, $this->connection->migrationName());
            $this->assertTrue(
                $this->connection->migration()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has no table samples"
            );
        });
    }

    /**
     * @test
     * @depends runs_migrate_on_tenants
     */
    public function runs_seed_on_tenants()
    {
        $this->migrateAndTest('migrate');

        $this->seedAndTest(function (Website $website) {
            $this->connection->set($website, $this->connection->migrationName());
            $this->assertTrue(
                $this->connection->migration()->table('samples')->count() === 1,
                "Connection for {$website->uuid} has no sample data seeded"
            );
        });
    }

    /**
     * @test
     * @depends runs_seed_on_tenants
     */
    public function runs_rollback_on_tenants()
    {
        $this->migrateAndTest('migrate:rollback', function (Website $website) {
            $this->connection->set($website, $this->connection->migrationName());
            $this->assertFalse(
                $this->connection->migration()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has table samples"
            );
        });
    }

    /**
     * @test
     * @depends runs_rollback_on_tenants
     */
    public function runs_refresh_on_tenants()
    {
        $this->migrateAndTest('migrate');

        $this->migrateAndTest('migrate:refresh', function (Website $website) {
            $this->connection->set($website, $this->connection->migrationName());
            $this->assertTrue(
                $this->connection->migration()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has no table samples"
            );
        });
    }

    /**
     * @test
     * @depends runs_refresh_on_tenants
     */
    public function runs_reset_on_tenants()
    {
        $this->migrateAndTest('migrate');

        $this->migrateAndTest('migrate:reset', function (Website $website) {
            $this->connection->set($website, $this->connection->migrationName());
            $this->assertFalse(
                $this->connection->migration()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has table samples"
            );
        });
    }
}
