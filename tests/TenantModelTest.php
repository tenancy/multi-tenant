<?php

namespace Laraflock\MultiTenant\Tests;

use HynMe\Framework\Testing\TestCase;
use Laraflock\MultiTenant\Models\Hostname;
use Laraflock\MultiTenant\Models\Tenant;

/**
 * Class TenantModeltest
 * @package Laraflock\MultiTenant\Tests
 * @coversDefaultClass \Laraflock\MultiTenant\Models\Tenant
 */
class TenantModeltest extends TestCase
{
    /**
     * @return Tenant
     * @coversNothing
     */
    public function testCreate()
    {
        $tenant = new Tenant;
        $tenant->name = 'example';
        $tenant->email = 'foo@baz.com';

        $hostname = new Hostname;
        $hostname->hostname = 'example.com';
        $hostname->tenant()->associate($tenant);

        return $tenant;
    }

    /**
     * Tests hostnames
     *
     * @param Tenant $tenant
     * @depends testCreate
     * @covers ::hostnames
     */
    public function testHostnames($tenant)
    {
        $this->assertGreaterThan(0, $tenant->hostnames);

        $this->assertEquals('example.com', $tenant->hostnames->first()->hostname);
    }
}