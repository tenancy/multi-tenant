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

use Hyn\Tenancy\Database\Console\Migrations\MigrateCommand;
use Hyn\Tenancy\Models\Website;

class MigrateCommandTest extends DatabaseCommandTest
{
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
    public function runs_migrate_on_one_tenant()
    {
        /** @var Website $otherWebsite */
        $otherWebsite = $this->getReplicatedWebsite();

        $this->connection->migrate($this->website, __DIR__ . '/../../migrations');

        $this->connection->set($this->website);

        $this->assertTrue($this->connection->get()->getSchemaBuilder()->hasTable('samples'));

        $this->connection->set($otherWebsite);

        $this->assertFalse($this->connection->get()->getSchemaBuilder()->hasTable('samples'));
    }

    /**
     * @test
     */
    public function runs_migrate_on_one_tenant_by_configuration()
    {
        /** @var Website $otherWebsite */
        $otherWebsite = $this->getReplicatedWebsite();

        config(['tenancy.db.tenant-migrations-path' => realpath(__DIR__ . '/../../migrations')]);

        $this->connection->migrate($this->website);

        $this->connection->set($this->website);

        $this->assertTrue($this->connection->get()->getSchemaBuilder()->hasTable('samples'));

        $this->connection->set($otherWebsite);

        $this->assertFalse($this->connection->get()->getSchemaBuilder()->hasTable('samples'));
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
    public function purges_connection_after_running_migrate_on_multiple_tenants()
    {
        $website = new Website();
        $this->websites->create($website);

        $this->assertEquals(2, $this->websites->query()->count());

        $connection = $this->swapConnectionWithSpy();
        $this->reloadArtisanCommand(MigrateCommand::class);

        $this->migrateAndTest('migrate');

        $connection->shouldHaveReceived('purge')->twice();
    }

    /**
     * @test
     */
    public function does_not_purge_connection_after_running_migrate_on_one_tenant()
    {
        $connection = $this->swapConnectionWithSpy();
        $this->reloadArtisanCommand(MigrateCommand::class);

        $this->migrateAndTest('migrate');

        $connection->shouldNotHaveReceived('purge');
    }
}
