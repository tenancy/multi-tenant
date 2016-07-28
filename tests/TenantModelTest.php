<?php

namespace Hyn\Tenancy\Tests;

use Hyn\Framework\Testing\TestCase;
use Hyn\Tenancy\Models\Customer;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Models\Website;

/**
 * Class CustomerModelTest.
 *
 * @coversDefaultClass \Hyn\Tenancy\Models\Tenant
 */
class TenantModelTest extends TestCase
{
    /**
     * @var Customer
     */
    protected $customer;

    public function setUp()
    {
        parent::setUp();

        $tenant = new Customer();
        $tenant->name = 'example';
        $tenant->email = 'foo@baz.com';

        $this->customer = $tenant;
    }

    /**
     * @test
     * @covers ::hostnames
     */
    public function hostnames_relation_is_correct()
    {
        $this->assertEquals(0, $this->customer->hostnames->count());

        $this->assertEquals(new Hostname(), $this->customer->hostnames()->getRelated()->newInstance([]));
    }

    /**
     * @test
     * @covers ::websites
     */
    public function websites_relation_is_correct()
    {
        $this->assertEquals(0, $this->customer->websites->count());

        $this->assertEquals(new Website(), $this->customer->websites()->getRelated()->newInstance([]));
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
        $this->assertEquals(0, $this->customer->reselled->count());
        $this->assertNull($this->customer->reseller);

        $this->assertEquals(0, $this->customer->refered->count());
        $this->assertNull($this->customer->referer);
    }

    /**
     * @test
     * @covers \Hyn\Tenancy\Presenters\TenantPresenter
     * @covers ::present
     */
    public function has_a_working_presenter()
    {
        $this->assertEquals($this->customer->name, $this->customer->present()->name);
        $this->assertNotNull($this->customer->present()->icon);
    }
}
