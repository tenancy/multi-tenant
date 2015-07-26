<?php

namespace Laraflock\MultiTenant\Tests;

use HynMe\Framework\Testing\TestCase;
use HynMe\Webserver\Models\SslCertificate;
use Laraflock\MultiTenant\Models\Hostname;
use Laraflock\MultiTenant\Models\Tenant;
use Laraflock\MultiTenant\Models\Website;

/**
 * Class HostnameModelTest
 * @package Laraflock\MultiTenant\Tests
 * @coversDefaultClass \Laraflock\MultiTenant\Models\Hostname
 */
class HostnameModelTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testCreate()
    {
        $hostname = new Hostname;
        $hostname->hostname = 'example.org';

        return $hostname;
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     */
    public function testHostname($hostname)
    {
        $this->assertEquals('example.org', $hostname->hostname);
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::tenant
     */
    public function testTenant($hostname)
    {
        $this->assertNull($hostname->tenant);

        $this->assertEquals(new Tenant, $hostname->tenant()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::website
     */
    public function testWebsite($hostname)
    {
        $this->assertNull($hostname->website);

        $this->assertEquals(new Website, $hostname->website()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::redirectToHostname
     */
    public function testRedirectTo($hostname)
    {
        $this->assertNull($hostname->redirectToHostname);

        $this->assertEquals(new Hostname, $hostname->redirectToHostname()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @covers ::certificate
     */
    public function testCertificate($hostname)
    {
        $this->assertNull($hostname->certificate);
        $this->assertEquals(new SslCertificate, $hostname->certificate()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @covers ::redirectActionRequired
     */
    public function testRedirectActionRequired($hostname)
    {
        $this->assertNull($hostname->redirectActionRequired);
    }
}