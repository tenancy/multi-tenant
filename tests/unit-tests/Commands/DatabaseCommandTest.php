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
        $this->migrateAndTest('migrate');

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

    /**
     * @test
     */
    public function after_creating_website_the_seed_ran()
    {
        config(['tenancy.db.tenant-migrations-path' => __DIR__ . '/../../migrations']);

        $this->assertNotNull(config('tenancy.db.tenant-migrations-path'));
        $website = new Website;
        $this->websites->create($website);
        $this->assertNotEquals(0, $website->id, "Website has not been created");
        $this->connection->set($website, $this->connection->migrationName());
        $this->assertTrue(
            $this->connection->migration()->table('samples')->count() === 0,
            "Connection for {$website->uuid} has sample data seeded"
        );

        config(['tenancy.db.tenant-seed-after-created-website' => \SampleSeeder::class]);
        $this->assertTrue(class_exists(config('tenancy.db.tenant-seed-after-created-website')), "Seed class doesnt exists.");
        $this->assertNotNull(config('tenancy.db.tenant-migrations-path'));
        $website = new Website;
        $this->websites->create($website);
        $this->assertNotEquals(0, $website->id, "Website has not been created");
        $this->connection->set($website, $this->connection->migrationName());
        $this->assertTrue(
            $this->connection->migration()->table('samples')->count() === 1,
            "Connection for {$website->uuid} has no sample data seeded"
        );
    }

    /**
     * @test
     */
    public function run_migration_on_one_database()
    {
        $website1 = new Website;
        $this->websites->create($website1);
        $this->assertNotEquals(0, $website1->id, "Website has not been created");

        $website2 = new Website;
        $this->websites->create($website2);
        $this->assertNotEquals(0, $website2->id, "Website has not been created");
        $this->migrateAndTest('migrate');

        $code = $this->artisan("tenancy:db:seed", [
            '--class' => 'SampleSeeder',
            '-n' => 1,
            '--websiteid' => $website1->id
        ]);

        $this->assertEquals(0, $code, "tenancy:db:seed didn't work out");

        $this->connection->set($website1, $this->connection->migrationName());
        $this->assertTrue(
            $this->connection->migration()->table('samples')->count() === 1,
            "Connection for {$website1->uuid} has no sample data seeded"
        );

        $this->connection->set($website2, $this->connection->migrationName());
        $this->assertTrue(
            $this->connection->migration()->table('samples')->count() === 0,
            "Connection for {$website2->uuid} has sample data"
        );
        $code = $this->artisan("tenancy:db:seed", [
            '--class' => 'SampleSeeder',
            '-n' => 1
        ]);

        $this->assertEquals(0, $code, "tenancy:db:seed didn't work out");
        $this->assertTrue(
            $this->connection->migration()->table('samples')->count() === 1,
            "Connection for {$website2->uuid} has no sample data"
        );
    }
}
