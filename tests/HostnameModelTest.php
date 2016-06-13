<?php

namespace Hyn\MultiTenant\Tests;

use Hyn\Framework\Testing\TestCase;
use Hyn\MultiTenant\Models\Customer;
use Hyn\MultiTenant\Models\Hostname;
use Hyn\MultiTenant\Models\Website;
use Hyn\Webserver\Models\SslCertificate;
use Illuminate\Http\RedirectResponse;

/**
 * Class HostnameModelTest.
 *
 * @coversDefaultClass \Hyn\MultiTenant\Models\Hostname
 */
class HostnameModelTest extends TestCase
{
    /**
     * @var Hostname
     */
    protected $hostname;

    public function setUp()
    {
        parent::setUp();

        $hostname = new Hostname();
        $hostname->hostname = 'example.org';

        $this->hostname = $hostname;
    }

    /**
     * @test
     */
    public function can_remember_hostname()
    {
        $this->assertEquals('example.org', $this->hostname->hostname);
    }

    /**
     * @test
     * @covers ::tenant
     */
    public function tenant_relation_is_correct()
    {
        $this->assertEquals(new Customer(), $this->hostname->customer()->getRelated()->newInstance([]));
    }

    /**
     * @test
     * @covers ::website
     */
    public function website_relation_is_correct()
    {
        $this->assertEquals(new Website(), $this->hostname->website()->getRelated()->newInstance([]));
    }

    /**
     * @test
     * @covers ::redirectToHostname
     */
    public function redirect_to_hostname_relation_is_correct()
    {
        $this->assertEquals(new Hostname(), $this->hostname->redirectToHostname()->getRelated()->newInstance([]));
    }

    /**
     * @test
     * @covers ::subDomainOf
     */
    public function sub_domain_of_relation_is_correct()
    {
        $this->assertEquals(new Hostname(), $this->hostname->subDomainOf()->getRelated()->newInstance([]));
    }

    /**
     * @test
     * @covers ::subDomains
     */
    public function sub_domains_relation_is_correct()
    {
        $this->assertEquals(new Hostname(), $this->hostname->subDomains()->getRelated()->newInstance([]));
    }

    /**
     * @test
     * @covers ::certificate
     */
    public function ssl_relation_is_correct()
    {
        $this->assertEquals(new SslCertificate(), $this->hostname->certificate()->getRelated()->newInstance([]));
    }

    /**
     * @test
     * @covers ::redirectActionRequired
     */
    public function has_to_redirect()
    {
        $this->assertTrue($this->hostname->redirectActionRequired() instanceof RedirectResponse);
    }

    /**
     * @test
     * @covers \Hyn\MultiTenant\Presenters\HostnamePresenter
     * @covers ::present
     */
    public function has_a_working_presenter()
    {
        $this->assertEquals($this->hostname->hostname, $this->hostname->present()->name);
        $this->assertNotNull($this->hostname->present()->icon);
    }
}
