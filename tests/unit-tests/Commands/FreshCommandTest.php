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

use Hyn\Tenancy\Database\Console\Migrations\FreshCommand;
use Hyn\Tenancy\Models\Website;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Hyn\Tenancy\Tests\Seeds\SampleSeeder;

class FreshCommandTest extends DatabaseCommandTest
{
    /**
     * @test
     */
    public function is_ioc_bound()
    {
        $this->assertInstanceOf(
            FreshCommand::class,
            $this->app->make(FreshCommand::class)
        );
    }

    /**
     * @test
     */
    public function runs_fresh_on_tenants()
    {
        $this->migrateAndTest('migrate:fresh', function (Website $website) {
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
    public function runs_fresh_with_seeding_on_tenants()
    {
        $this->migrateAndTest('migrate:fresh', function (Website $website) {
            $this->connection->set($website);
            $this->assertTrue(
                $this->connection->get()->getSchemaBuilder()->hasTable('samples'),
                "Connection for {$website->uuid} has no table samples"
            );
        }, null, [
            '--seed' => 1,
            '--seeder' => SampleSeeder::class
        ]);
    }

    /**
     * @test
     */
    public function purges_connection_after_running_fresh_on_multiple_tenants()
    {
        $website = new Website();
        $this->websites->create($website);

        $this->assertEquals(2, $this->websites->query()->count());

        $connection = $this->swapConnectionWithSpy();
        $this->reloadArtisanCommand(FreshCommand::class);

        $this->migrateAndTest('migrate:fresh');

        $connection->shouldHaveReceived('purge')->twice();
    }

    /**
     * @test
     */
    public function does_not_purge_connection_after_running_fresh_on_one_tenant()
    {
        $connection = $this->swapConnectionWithSpy();
        $this->reloadArtisanCommand(FreshCommand::class);

        $this->migrateAndTest('migrate:fresh');

        $connection->shouldNotHaveReceived('purge');
    }

    /**
     * In the prefix division mode every tenant and the system tables share one
     * database, so a wipe aimed at the connection empties all of them.
     *
     * @test
     */
    public function running_fresh_leaves_the_system_and_the_other_tenants_alone()
    {
        $other = new Website();
        $this->websites->create($other);

        // A table of the other tenant's, to notice the loss of.
        $this->connection->set($other);
        $this->connection->get()->getSchemaBuilder()->create('canary', function (Blueprint $table) {
            $table->increments('id');
        });
        $this->connection->purge();

        $this->migrateAndTest('migrate:fresh', null, null, [
            '--website_id' => [$this->website->id],
        ]);

        $this->assertTrue(
            $this->connection->system()->getSchemaBuilder()->hasTable('websites'),
            'A tenant migrate:fresh dropped the system websites table.'
        );

        $this->connection->set($other);
        $this->assertTrue(
            $this->connection->get()->getSchemaBuilder()->hasTable('canary'),
            "Tenant {$other->uuid} lost its tables to another tenant's migrate:fresh."
        );
    }

    protected function duringSetUp(Application $app)
    {
        $this->setUpWebsites(true);
    }
}
