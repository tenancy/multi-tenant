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

namespace Hyn\Tenancy\Tests\Isolation;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Tests\Extend\AwareExtend;
use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Tests\Traits\InteractsWithIsolation;
use Illuminate\Contracts\Foundation\Application;

/**
 * Whether one tenant's data can ever be reached while another is active.
 *
 * The assertions name the tenant whose data leaked rather than report a
 * mismatched count, since a failure here means one customer reading another's.
 */
class ConnectionIsolationTest extends Test
{
    use InteractsWithIsolation;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpIsolation();
    }

    /**
     * @test
     */
    public function switching_between_tenants_shows_only_that_tenant()
    {
        $this->asTenant($this->tenantA, fn () => $this->assertOnlySees(self::MARKER_A, 'first switch to A'));
        $this->asTenant($this->tenantB, fn () => $this->assertOnlySees(self::MARKER_B, 'switch to B'));
        $this->asTenant($this->tenantA, fn () => $this->assertOnlySees(self::MARKER_A, 'switch back to A'));
    }

    /**
     * @test
     */
    public function switching_without_an_explicit_purge_still_isolates()
    {
        // Connection::set() is expected to purge and reconnect on its own. If
        // it relies on callers remembering to purge first, this fails.
        $this->connection->set($this->tenantA);
        $this->assertOnlySees(self::MARKER_A, 'set A without purging');

        $this->connection->set($this->tenantB);
        $this->assertOnlySees(self::MARKER_B, 'set B directly after A, no purge in between');

        $this->connection->purge();
    }

    /**
     * @test
     */
    public function releasing_the_tenant_does_not_leave_the_previous_one_reachable()
    {
        // The vector this exists for: after the active tenant is released, a
        // model that always asks for the tenant connection must not quietly
        // reconnect using the previous tenant's credentials.
        $this->connection->set($this->tenantA);
        $this->assertOnlySees(self::MARKER_A, 'A is active');

        $this->releaseTenant();

        $leaked = null;

        try {
            $leaked = $this->visibleMarkers();
        } catch (\Throwable $e) {
            // Refusing to connect is the correct outcome: no tenant is active,
            // so there is nothing legitimate to read.
            $this->assertTrue(true);

            return;
        }

        $this->fail(
            'TENANT ISOLATION FAILURE: after releasing the tenant, a query on the '
            .'tenant connection still returned ['.implode(', ', $leaked).']. '
            .'The connection kept the previous tenant\'s configuration.'
        );
    }

    /**
     * @test
     */
    public function setting_a_null_tenant_does_not_leave_the_previous_configuration_armed()
    {
        // ConnectsTenants calls Connection::set() with whatever a Websites
        // event carries, and that can be null. Unlike purge(), set(null) closes
        // the connection but leaves the tenant's credentials in config, from
        // which the next model asking for the connection reopens them.
        $this->connection->set($this->tenantA);
        $this->assertOnlySees(self::MARKER_A, 'A is active');

        $this->connection->set(null);

        $configured = $this->connection->configuration();

        $this->assertSame(
            [],
            $configured,
            'TENANT ISOLATION FAILURE: after set(null) the tenant connection is '
            .'still configured for '.($configured['database'] ?? 'a previous tenant')
            .'. Any model using UsesTenantConnection will reconnect straight into it.'
        );
    }

    /**
     * @test
     */
    public function reading_after_a_null_tenant_never_returns_the_previous_tenants_rows()
    {
        // The same defect stated as consequence rather than as state: what a
        // query actually returns once no tenant is meant to be active.
        $this->connection->set($this->tenantA);
        $this->assertOnlySees(self::MARKER_A, 'A is active');

        $this->connection->set(null);

        try {
            $seen = $this->visibleMarkers();
        } catch (\Throwable $e) {
            // Refusing to connect is the correct outcome.
            $this->assertTrue(true);

            return;
        }

        $this->fail(
            'TENANT ISOLATION FAILURE: with no tenant active, a query on the tenant '
            .'connection returned ['.implode(', ', $seen).']. set(null) left the '
            ."previous tenant's credentials in place."
        );
    }

    /**
     * @test
     */
    public function a_tenant_aware_model_falls_back_to_system_rather_than_the_last_tenant()
    {
        $this->connection->set($this->tenantA);
        $this->assertSame($this->connection->tenantName(), (new AwareExtend())->getConnectionName());

        $this->releaseTenant();

        $this->assertSame(
            $this->connection->systemName(),
            (new AwareExtend())->getConnectionName(),
            'With no tenant active a tenant-aware model must use the system connection, '
            .'never the connection left behind by the previous tenant.'
        );
    }

    /**
     * @test
     */
    public function a_system_model_ignores_whichever_tenant_is_active()
    {
        $websites = $this->connection->systemName();

        $this->asTenant($this->tenantA, function () use ($websites) {
            $this->assertSame($websites, (new \Hyn\Tenancy\Models\Website())->getConnectionName());
        });

        $this->asTenant($this->tenantB, function () use ($websites) {
            $this->assertSame($websites, (new \Hyn\Tenancy\Models\Website())->getConnectionName());
        });
    }

    /**
     * @test
     */
    public function writes_land_in_the_active_tenant_only()
    {
        $this->asTenant($this->tenantA, function () {
            $this->writeMarker('written-while-a-was-active');
        });

        $this->asTenant($this->tenantB, function () {
            $this->assertOnlySees(self::MARKER_B, 'B must not see a row written while A was active');
        });

        $this->asTenant($this->tenantA, function () {
            $this->assertSame(
                ['belongs-to-tenant-a', 'written-while-a-was-active'],
                $this->visibleMarkers(),
                'The row written while A was active should be in A.'
            );
        });
    }

    /**
     * @test
     */
    public function each_tenant_gets_its_own_slice_of_the_server()
    {
        $a = $this->connection->generateConfigurationArray($this->tenantA);
        $b = $this->connection->generateConfigurationArray($this->tenantB);

        $this->assertSame($this->tenantA->uuid, $a['uuid']);
        $this->assertSame($this->tenantB->uuid, $b['uuid']);

        // Assert on whichever separator the configured mode hands out.
        switch (config('tenancy.db.tenant-division-mode')) {
            case Connection::DIVISION_MODE_SEPARATE_DATABASE:
                $this->assertNotSame($a['database'], $b['database'], 'Tenants must not share a database name.');
                $this->assertNotSame($a['username'], $b['username'], 'Tenants must not share a database user.');
                $this->assertNotSame($a['password'], $b['password'], 'Tenants must not share a database password.');
                break;

            case Connection::DIVISION_MODE_SEPARATE_SCHEMA:
                $this->assertNotSame($a['schema'], $b['schema'], 'Tenants must not share a schema.');
                $this->assertNotSame($a['username'], $b['username'], 'Tenants must not share a database user.');
                break;

            case Connection::DIVISION_MODE_SEPARATE_PREFIX:
                $this->assertNotSame($a['prefix'], $b['prefix'], 'Tenants must not share a table prefix.');
                break;

            default:
                $this->markTestSkipped('This division mode does not separate tenants at the connection level.');
        }
    }

    /**
     * @test
     */
    public function one_tenant_cannot_read_another_tenants_data_even_with_its_own_credentials()
    {
        // The last line of defence: with a connection aimed straight at
        // another tenant's database, the server itself must refuse the rows.
        //
        // Only the outcome is asserted. MySQL and MariaDB reject the connection,
        // PostgreSQL refuses at CONNECT or, on databases provisioned before the
        // revoke, at the table. Any refusal will do; returning rows will not.
        if (config('tenancy.db.tenant-division-mode') !== Connection::DIVISION_MODE_SEPARATE_DATABASE) {
            $this->markTestSkipped(
                'Only the database division mode hands each tenant its own credentials, so there are '
                .'none to cross here. The other modes separate tenants inside one database, which the '
                .'switching tests in this class cover.'
            );
        }

        $a = $this->connection->generateConfigurationArray($this->tenantA);
        $b = $this->connection->generateConfigurationArray($this->tenantB);

        $crossed = $a;
        $crossed['database'] = $b['database'];
        $crossed['search_path'] = $b['search_path'] ?? null;

        config(['database.connections.crossed-tenant' => $crossed]);

        $rows = null;

        try {
            $rows = app('db')->connection('crossed-tenant')
                ->table('samples')
                ->pluck('name')
                ->all();
        } catch (\Throwable $e) {
            // Refused, at whichever layer. That is the point.
            $this->assertTrue(true);

            return;
        } finally {
            app('db')->purge('crossed-tenant');
        }

        $this->fail(
            "TENANT ISOLATION FAILURE: tenant A's credentials read ["
            .implode(', ', $rows)."] out of tenant B's database ({$b['database']}). "
            .'The database user has wider grants than the separate-database '
            .'division mode assumes.'
        );
    }
}
