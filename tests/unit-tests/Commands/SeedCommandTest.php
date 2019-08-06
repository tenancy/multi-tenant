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

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Console\Seeds\SeedCommand;
use Hyn\Tenancy\Models\Website;
use Illuminate\Contracts\Console\Kernel;
use SampleSeeder;

class SeedCommandTest extends DatabaseCommandTest
{
    /**
     * @test
     */
    public function is_ioc_bound()
    {
        $this->assertInstanceOf(
            SeedCommand::class,
            $this->app->make(SeedCommand::class)
        );
    }

    /**
     * @test
     */
    public function runs_seed_on_one_tenant()
    {
        /** @var Website $otherWebsite */
        $otherWebsite = $this->getReplicatedWebsite();

        $this->migrateAndTest('migrate');

        $this->assertTrue(
            $this->connection->seed($this->website, SampleSeeder::class),
            "Seeding command failed {$this->website->uuid}."
        );

        $this->connection->set($this->website);

        $this->assertFalse($this->connection->get()->getDoctrineSchemaManager()->tablesExist('users'));
        $this->assertTrue($this->connection->get()->getDoctrineSchemaManager()->tablesExist('samples'));

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
    public function runs_configured_seed()
    {
        $this->migrateAndTest('migrate');

        config(['tenancy.db.tenant-seed-class' => SampleSeeder::class]);

        // We need to register the command anew to have it pick up the seeder class override from the config.
        // This also means we cannot declare the seed class dynamically during runtime.
        $this->app[Kernel::class]->registerCommand(new SeedCommand($this->app['db']));

        $this->artisan('tenancy:db:seed', [
            '-n' => 1,
            '--force' => true
        ]);

        $this->connection->set($this->website);

        $this->assertFalse($this->connection->get()->getDoctrineSchemaManager()->tablesExist('users'));
        $this->assertTrue($this->connection->get()->getDoctrineSchemaManager()->tablesExist('samples'));
    }

    /**
     * @test
     */
    public function runs_seed_on_tenants()
    {
        $this->connection->set($this->website);

        $this->assertFalse($this->connection->get()->getDoctrineSchemaManager()->tablesExist('samples'));

        $this->migrateAndTest('migrate');

        $this->seedAndTest(function (Website $website) {
            $this->connection->set($website);

            $this->assertTrue($this->connection->get()->getDoctrineSchemaManager()->tablesExist('samples'));

            $this->assertEquals(
                2, $this->connection->get()->table('samples')->count(),
                "Connection for {$website->uuid} has incorrect sample data"
            );
        });
    }

    /**
     * @test
     */
    public function purges_connection_after_running_seed_on_multiple_tenants()
    {
        $website = new Website();
        $this->websites->create($website);

        $this->assertEquals(2, $this->websites->query()->count());

        $connection = $this->swapConnectionWithSpy();
        $this->reloadArtisanCommand(SeedCommand::class);

        $this->migrateAndTest('migrate');

        $this->seedAndTest();

        $connection->shouldHaveReceived('purge')->twice();
    }

    /**
     * @test
     */
    public function does_not_purge_connection_after_running_seed_on_one_tenant()
    {
        $this->migrateAndTest('migrate');

        $connection = $this->swapConnectionWithSpy();
        $this->reloadArtisanCommand(SeedCommand::class);

        $this->connection->seed($this->website, SampleSeeder::class);

        $connection->shouldNotHaveReceived('purge');
    }
}
