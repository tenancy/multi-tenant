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
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Providers\Tenants\ConnectionProvider;
use Hyn\Tenancy\Tests\Extend\NonExtend;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Database\Connection as DatabaseConnection;
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
}
