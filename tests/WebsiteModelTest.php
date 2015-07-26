<?php

namespace Laraflock\MultiTenant\Tests;

use HynMe\Framework\Testing\TestCase;
use Laraflock\MultiTenant\Models\Website;

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
     * @depends testCreate
     */
    public function testHostnames($website)
    {
        $this->assertEquals(0, $website->hostnames->count());


    }
}