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

namespace Hyn\Tenancy\Tests\Database;

use Doctrine\DBAL\Driver\PDOException;
use Hyn\Tenancy\Commands\UpdateKeyCommand;
use Hyn\Tenancy\Contracts\CurrentHostname;
use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Providers\Tenants\ConnectionProvider;
use Hyn\Tenancy\Tests\Extend\NonExtend;
use Hyn\Tenancy\Events\Database\ConnectionSet;
use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Database\Connection as DatabaseConnection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class ConnectionTest extends Test
{
    /**
     * @test
     */
    public function without_identification_no_tenant_connection_is_active()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->setUpHostnames(true);

        $this->connection->get();
    }

    /**
     * @test
     * @depends without_identification_no_tenant_connection_is_active
     */
    public function hostname_identification_switches_connection()
    {
        $this->setUpHostnames(true);
        $this->app->make(CurrentHostname::class);

        $failsWithoutWebsite = false;

        try {
            $this->connection->get();
        } catch (\InvalidArgumentException $e) {
            $failsWithoutWebsite = true;
        }

        $this->assertTrue($failsWithoutWebsite, 'Tenant connection should not work, when the hostname has no website.');
        $this->assertTrue($this->connection->system() instanceof DatabaseConnection, 'System connection is not working.');
    }

    /**
     * @test
     * @depends hostname_identification_switches_connection
     */
    public function both_connections_work()
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
        $this->activateTenant();

        $this->assertTrue($this->connection->get() instanceof DatabaseConnection, 'Tenant connection is not set up properly.');
        $this->assertTrue($this->connection->system() instanceof DatabaseConnection, 'System connection fails once tenant connection is set up.');
    }

    /**
     * @test
     * @depends both_connections_work
     */
    public function can_migrate_the_tenant()
    {
        config(['tenancy.db.tenant-migrations-path' => __DIR__ . '/../../migrations']);

        $this->assertNotNull(config('tenancy.db.tenant-migrations-path'));

        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
        $this->activateTenant();

        $this->assertTrue($this->connection->get()->getSchemaBuilder()->hasTable('samples'));
    }

    /**
     * @test
     */
    public function override_to_tenant_connection()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Database connection [tenant] not configured.');

        config(['tenancy.db.force-tenant-connection-of-models' => [NonExtend::class]]);

        // Run the connection provider again to read this new model.
        (new ConnectionProvider($this->app))->overrideConnectionResolvers();

        (new NonExtend())->getConnection()->getConfig();
    }

    /**
     * @test
     */
    public function override_to_system_connection()
    {
        config(['tenancy.db.force-system-connection-of-models' => [NonExtend::class]]);

        // Run the connection provider again to read this new model.
        (new ConnectionProvider($this->app))->overrideConnectionResolvers();

        $this->assertEquals($this->connection->systemName(), (new NonExtend())->getConnection()->getName());
    }

    /**
     * @test
     */
    public function can_rotate_tenant_key()
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);
        $this->activateTenant();

        // Check that connection is established before rotating TENANCY_KEY
        $this->assertTrue($this->connection->get() instanceof DatabaseConnection, 'Tenant connection is not set up properly.');

        config(['tenancy.key' => Str::random()]);

        // Re-establish connection and expect 1045 error code (Access denied for user)
        app(Environment::class)->tenant($this->website);
        try {
            $this->connection->get()->reconnect();
        } catch (PDOException $e) {
            $this->assertTrue($e->getCode() === 1045 || $e->getCode() === 7, 'Access should be denied for tenant database user: [code: '.$e->getCode().'] '. $e->getMessage());
        }

        $this->artisan(UpdateKeyCommand::class);

        // Re-establish connection after updating tenant users password
        app(Environment::class)->tenant($this->website);
        $this->connection->get()->reconnect();
    }

    /**
     * A name configured as empty is not a connection called nothing. The
     * second argument of get() only covers a key that is missing altogether.
     *
     * @test
     */
    public function an_empty_connection_name_falls_back_to_the_default()
    {
        $previous = [
            'tenancy.db.tenant-connection-name' => config('tenancy.db.tenant-connection-name'),
            'tenancy.db.system-connection-name' => config('tenancy.db.system-connection-name'),
        ];

        try {
            foreach ([null, ''] as $empty) {
                config([
                    'tenancy.db.tenant-connection-name' => $empty,
                    'tenancy.db.system-connection-name' => $empty,
                ]);

                $this->assertEquals(Connection::DEFAULT_TENANT_NAME, $this->connection->tenantName());
                $this->assertEquals(Connection::DEFAULT_SYSTEM_NAME, $this->connection->systemName());
            }
        } finally {
            // Left behind, these names reach teardown and it cannot find the
            // connection it is asked to clean up.
            config($previous);
        }
    }

    /**
     * The credentials are derived from the website, its id among them, so the
     * same uuid can come back needing a different password. Keeping the open
     * connection then means reconnecting with the old one.
     *
     * @test
     */
    public function a_tenant_whose_credentials_changed_purges_the_connection()
    {
        $this->setUpWebsites(true);

        $this->connection->set($this->website);

        $purged = [];

        Event::listen(ConnectionSet::class, function (ConnectionSet $event) use (&$purged) {
            $purged[] = $event->purged;
        });

        $this->connection->set($this->website);

        $this->assertSame([false], $purged, 'Nothing changed, so nothing had to be purged.');

        $sameTenantNewCredentials = Website::unguarded(function () {
            return new Website(['uuid' => $this->website->uuid]);
        });

        $sameTenantNewCredentials->id = $this->website->id + 1000;

        $this->connection->set($sameTenantNewCredentials);

        $this->assertSame([false, true], $purged);
    }

    /**
     * A database user surviving from an earlier tenant of the same name holds
     * a password that no longer opens anything. Provisioning has to set it,
     * not assume it.
     *
     * @test
     */
    public function provisioning_over_a_surviving_database_user_still_connects()
    {
        if (config('tenancy.db.tenant-division-mode') !== Connection::DIVISION_MODE_SEPARATE_DATABASE) {
            $this->markTestSkipped('Only the database division mode gives a tenant a database of its own.');
        }

        $website = new Website();
        $this->websites->create($website);

        $uuid = $website->uuid;

        // Delete the tenant but leave its user behind, which is what a failed
        // drop leaves in place.
        config(['tenancy.db.auto-delete-tenant-database-user' => false]);

        $this->websites->delete($website, true);

        config(['tenancy.db.auto-delete-tenant-database-user' => true]);

        $again = Website::unguarded(function () use ($uuid) {
            return new Website(['uuid' => $uuid]);
        });

        $this->websites->create($again);

        $reachable = true;

        try {
            $this->connection->set($again);
            $this->connection->get()->getSchemaBuilder()->hasTable('a_table_no_tenant_has');
        } catch (\Throwable $e) {
            $reachable = false;
        } finally {
            $this->connection->purge();
        }

        $this->assertTrue(
            $reachable,
            "Tenant $uuid was provisioned over a surviving user and cannot connect."
        );
    }
}
