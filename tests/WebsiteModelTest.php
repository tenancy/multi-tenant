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
     * @var Website
     */
    protected $website;

    public function setUp()
    {
        parent::setUp();

        $website = new Website();
        $website->identifier = 'example-com';

        $this->website = $website;
    }

    /**
     * @test
     * @covers ::hostnames
     * @covers ::getHostnamesWithCertificateAttribute
     * @covers ::getHostnamesWithoutCertificateAttribute
     */
    public function hostnames_relation_is_correct()
    {
        $this->assertEquals(new Hostname(), $this->website->hostnames()->getRelated()->newInstance());
    }

    /**
     * @test
     * @covers ::getDirectoryAttribute
     */
    public function directory_property_is_correct()
    {
        $this->assertEquals(new Directory($this->website), $this->website->directory);
    }

    /**
     * @test
     * @covers \Hyn\MultiTenant\Presenters\WebsitePresenter
     * @covers ::present
     */
    public function has_a_working_presenter()
    {
        $this->assertEquals($this->website->identifier, $this->website->present()->name);
        $this->assertNotNull($this->website->present()->icon);
    }
}
