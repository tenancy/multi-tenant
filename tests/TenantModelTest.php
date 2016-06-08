<?php

namespace Hyn\MultiTenant\Tests;

use Hyn\Framework\Testing\TestCase;
use Hyn\MultiTenant\Models\Hostname;
use Hyn\MultiTenant\Models\Tenant;
use Hyn\MultiTenant\Models\Website;

/**
 * Class TenantModeltest.
 *
 * @coversDefaultClass \Hyn\MultiTenant\Models\Tenant
 */
class TenantModelTest extends TestCase
{
    /**
     * @var Tenant
     */
    protected $tenant;

    public function setUp()
    {
        parent::setUp();

        $tenant = new Tenant();
        $tenant->name = 'example';
        $tenant->email = 'foo@baz.com';

        $this->tenant = $tenant;
    }

    /**
     * @test
     * @covers ::hostnames
     */
    public function hostnames_relation_is_correct()
    {
        $this->assertEquals(0, $this->tenant->hostnames->count());

        $this->assertEquals(new Hostname(), $this->tenant->hostnames()->getRelated()->newInstance([]));
    }

    /**
     * @test
     * @covers ::websites
     */
    public function websites_relation_is_correct()
    {
        $this->assertEquals(0, $this->tenant->websites->count());

        $this->assertEquals(new Website(), $this->tenant->websites()->getRelated()->newInstance([]));
    }

    /**
     * @test
     * @covers ::reseller
     * @covers ::referer
     * @covers ::refered
     * @covers ::reselled
     */
    public function validates_initial_state_of_customer_relations()
    {
        $this->assertEquals(0, $this->tenant->reselled->count());
        $this->assertNull($this->tenant->reseller);

        $this->assertEquals(0, $this->tenant->refered->count());
        $this->assertNull($this->tenant->referer);
    }

    /**
     * @test
     * @covers \Hyn\MultiTenant\Presenters\TenantPresenter
     * @covers ::present
     */
    public function has_a_working_presenter()
    {
        $this->assertEquals($this->tenant->name, $this->tenant->present()->name);
        $this->assertNotNull($this->tenant->present()->icon);
    }
}
