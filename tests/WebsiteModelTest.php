<?php

namespace Hyn\MultiTenant\Tests;

use Hyn\Framework\Testing\TestCase;
use Hyn\MultiTenant\Models\Hostname;
use Hyn\MultiTenant\Models\Website;
use Hyn\MultiTenant\Tenant\Directory;

/**
 * Class WebsiteModelTest.
 *
 * @coversDefaultClass \Hyn\MultiTenant\Models\Website
 */
class WebsiteModelTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testCreate()
    {
        $website = new Website();
        $website->identifier = 'example-com';

        return $website;
    }

    /**
     * @param Website $website
     * @covers ::hostnames
     * @covers ::getHostnamesWithCertificateAttribute
     * @covers ::getHostnamesWithoutCertificateAttribute
     * @depends testCreate
     */
    public function testHostnames($website)
    {
        $this->assertEquals(new Hostname(), $website->hostnames()->getRelated()->newInstance());
    }

    /**
     * @param Website $website
     * @covers ::getDirectoryAttribute
     * @depends testCreate
     */
    public function testDirectoryAttribute($website)
    {
        $this->assertEquals(new Directory($website), $website->directory);
    }

    /**
     * @param Website $website
     * @depends testCreate
     * @covers \Hyn\MultiTenant\Presenters\WebsitePresenter
     */
    public function testPresenter($website)
    {
        $this->assertEquals($website->identifier, $website->present()->name);
        $this->assertNotNull($website->present()->icon);
    }
}
