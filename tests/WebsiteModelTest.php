<?php

namespace Laraflock\MultiTenant\Tests;

use HynMe\Framework\Testing\TestCase;
use Laraflock\MultiTenant\Models\Hostname;
use Laraflock\MultiTenant\Models\Website;
use Laraflock\MultiTenant\Tenant\Directory;

/**
 * Class WebsiteModelTest
 * @package Laraflock\MultiTenant\Tests
 * @coversDefaultClass \laraflock\MultiTenant\Models\Website
 */
class WebsiteModelTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testCreate()
    {
        $website = new Website;
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
        $this->assertEquals(0, $website->hostnames->count());
        $this->assertEquals(new Hostname, $website->hostnames()->getRelated()->newInstance());
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
}