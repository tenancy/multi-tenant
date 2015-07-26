<?php

namespace Laraflock\MultiTenant\Tests;

use HynMe\Framework\Testing\TestCase;
use HynMe\Webserver\Models\SslCertificate;
use Illuminate\Http\RedirectResponse;
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
        $this->assertEquals(new Tenant, $hostname->tenant()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::website
     */
    public function testWebsite($hostname)
    {
        $this->assertEquals(new Website, $hostname->website()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::redirectToHostname
     */
    public function testRedirectTo($hostname)
    {
        $this->assertEquals(new Hostname, $hostname->redirectToHostname()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::subDomainOf
     */
    public function testSubDomainOf($hostname)
    {
        $this->assertEquals(new Hostname, $hostname->subDomainOf()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     */
    public function testSubDomains($hostname)
    {
        $this->assertEquals(new Hostname, $hostname->subDomains()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::certificate
     */
    public function testCertificate($hostname)
    {
        $this->assertEquals(new SslCertificate, $hostname->certificate()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::redirectActionRequired
     */
    public function testRedirectActionRequired($hostname)
    {
        $this->assertTrue($hostname->redirectActionRequired() instanceof RedirectResponse);
    }
}