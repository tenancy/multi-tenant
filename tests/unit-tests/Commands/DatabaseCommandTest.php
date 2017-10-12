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

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Database\Console\Migrations\MigrateCommand;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Tests\Traits\InteractsWithMigrations;
use Illuminate\Contracts\Foundation\Application;
use SampleSeeder;

class DatabaseCommandTest extends Test
{
    use InteractsWithMigrations;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);

        $this->connection = $app->make(Connection::class);
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
     * @return Website
     */
    public function runs_migrate_on_one_tenant()
    {
        $this->connection->migrate($this->website, __DIR__ . '/../../migrations');

        $this->connection->set($this->website);

        $this->assertTrue($this->connection->get()->getSchemaBuilder()->hasTable('samples'));

        $otherWebsite = $this->website->replicate();
        $this->websites->create($otherWebsite);

        $this->connection->set($otherWebsite);

        $this->assertFalse($this->connection->get()->getSchemaBuilder()->hasTable('samples'));
    }

    /**
     * @test
     */
    public function runs_seed_on_one_tenant()
    {
        /** @var Website $otherWebsite */
        $otherWebsite = $this->website->replicate();
        $this->websites->create($otherWebsite);

        $this->migrateAndTest('migrate');

        $this->assertTrue(
            $this->connection->seed($this->website, SampleSeeder::class),
            "Seeding command failed {$this->website->uuid}."
        );

        $this->connection->set($this->website);

        $this->assertGreaterThan(
            0,
            $this->connection->get()->table('samples')->count(),
            "Unable to seed one single tenant {$this->website->uuid}."
        );

        $this->connection->set($otherWebsite);

        $this->assertEquals(
            0,
            $this->connection->get()->table('samples')->count(),
            "Seeding one tenant also affected another tenant {$otherWebsite->uuid}."
        );
    }

    /**
     * @test
     */
    public function runs_migrate_on_tenants()
    {
        $this->migrateAndTest('migrate', function (Website $website) {
            $this->connection->set($website);

            $this->assertTrue(
                $this->connection->get()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has no table samples"
            );
        });
    }

    /**
     * @test
     */
    public function runs_seed_on_tenants()
    {
        $this->migrateAndTest('migrate');

        $this->seedAndTest(function (Website $website) {
            $this->connection->set($website);

            $this->assertTrue(
                $this->connection->get()->table('samples')->count() === 1,
                "Connection for {$website->uuid} has no sample data seeded"
            );
        });
    }

    /**
     * @test
     */
    public function runs_rollback_on_tenants()
    {
        $this->migrateAndTest('migrate');

        $this->migrateAndTest('migrate:rollback', function (Website $website) {
            $this->connection->set($website);
            $this->assertFalse(
                $this->connection->get()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has table samples"
            );
        });
    }

    /**
     * @test
     */
    public function runs_refresh_on_tenants()
    {
        $this->migrateAndTest('migrate');

        $this->migrateAndTest('migrate:refresh', function (Website $website) {
            $this->connection->set($website);
            $this->assertTrue(
                $this->connection->get()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has no table samples"
            );
        });
    }

    /**
     * @test
     */
    public function runs_reset_on_tenants()
    {
        $this->migrateAndTest('migrate');

        $this->migrateAndTest('migrate:reset', function (Website $website) {
            $this->connection->set($website);
            $this->assertFalse(
                $this->connection->get()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has table samples"
            );
        });
    }
}
