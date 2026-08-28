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

use Hyn\Tenancy\Contracts\Website;
use Hyn\Tenancy\Environment;
use Hyn\Tenancy\Tests\Extend\TenantExtend;

/**
 * Fixtures for the isolation tests.
 *
 * Two tenants are provisioned, each with the sample table migrated and one row
 * carrying a marker of its own. Any query returning another number of rows, or
 * the wrong marker, is a leak.
 */
trait InteractsWithIsolation
{
    /** @var Website */
    protected $tenantA;

    /** @var Website */
    protected $tenantB;

    protected const MARKER_A = 'belongs-to-tenant-a';

    protected const MARKER_B = 'belongs-to-tenant-b';

    /**
     * Provision two tenants, each holding one identifiable row.
     */
    protected function setUpIsolation(): void
    {
        $this->tenantA = $this->createIsolatedTenant(self::MARKER_A);
        $this->tenantB = $this->createIsolatedTenant(self::MARKER_B);

        // Leave nothing active, so a test that forgets to switch fails loudly
        // instead of quietly inheriting whichever tenant was created last.
        $this->releaseTenant();
    }

    protected function createIsolatedTenant(string $marker): Website
    {
        $website = new \Hyn\Tenancy\Models\Website();
        $this->websites->create($website);

        $this->connection->migrate($website, __DIR__.'/../migrations');

        $this->connection->set($website);
        $this->writeMarker($marker);
        $this->connection->purge();

        return $website;
    }

    /**
     * Write a marker row on whichever tenant connection is active.
     *
     * TenantExtend declares no fillable attributes, hence the direct
     * assignment.
     */
    protected function writeMarker(string $marker): void
    {
        $row = new TenantExtend();
        $row->name = $marker;
        $row->save();
    }

    /**
     * Run a callback with the given tenant active, then release it.
     */
    protected function asTenant(Website $website, callable $callback)
    {
        $this->connection->set($website);

        try {
            return $callback();
        } finally {
            $this->connection->purge();
        }
    }

    /**
     * Drop any active tenant, both the connection and the resolved instance.
     */
    protected function releaseTenant(): void
    {
        $this->connection->purge();
        app(Environment::class)->tenant(null);
    }

    /**
     * The markers visible on the tenant connection right now.
     */
    protected function visibleMarkers(): array
    {
        return TenantExtend::query()->orderBy('name')->pluck('name')->all();
    }

    /**
     * Assert that exactly the expected tenant's data is reachable.
     *
     * Reports the markers that were visible, so a leak names the tenant whose
     * rows appeared.
     */
    protected function assertOnlySees(string $marker, string $context = ''): void
    {
        $seen = $this->visibleMarkers();
        $where = $context === '' ? '' : " ({$context})";

        $this->assertSame(
            [$marker],
            $seen,
            "TENANT ISOLATION FAILURE{$where}: expected to see only [{$marker}], "
            ."saw [".implode(', ', $seen).'].'
        );
    }
}
