<?php

namespace Hyn\MultiTenant\Tests;

use Hyn\Framework\Testing\TestCase;
use Hyn\Webserver\Models\SslCertificate;
use Illuminate\Http\RedirectResponse;
use Hyn\MultiTenant\Models\Hostname;
use Hyn\MultiTenant\Models\Tenant;
use Hyn\MultiTenant\Models\Website;

/**
 * Class HostnameModelTest.
 *
 * @coversDefaultClass \Hyn\MultiTenant\Models\Hostname
 */
class HostnameModelTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testCreate()
    {
        $hostname = new Hostname();
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
        $this->assertEquals(new Tenant(), $hostname->tenant()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::website
     */
    public function testWebsite($hostname)
    {
        $this->assertEquals(new Website(), $hostname->website()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::redirectToHostname
     */
    public function testRedirectTo($hostname)
    {
        $this->assertEquals(new Hostname(), $hostname->redirectToHostname()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::subDomainOf
     */
    public function testSubDomainOf($hostname)
    {
        $this->assertEquals(new Hostname(), $hostname->subDomainOf()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     */
    public function testSubDomains($hostname)
    {
        $this->assertEquals(new Hostname(), $hostname->subDomains()->getRelated()->newInstance([]));
    }

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers ::certificate
     */
    public function testCertificate($hostname)
    {
        $this->assertEquals(new SslCertificate(), $hostname->certificate()->getRelated()->newInstance([]));
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

    /**
     * @param Hostname $hostname
     * @depends testCreate
     * @covers \Hyn\MultiTenant\Presenters\HostnamePresenter
     */
    public function testHostnamePresenter($hostname)
    {
        $this->assertEquals($hostname->hostname, $hostname->present()->name);
        $this->assertNotNull($hostname->present()->icon);
    }
}
