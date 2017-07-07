<?php

namespace Hyn\Tenancy\Tests\Repositories;

use Hyn\Tenancy\Contracts\Repositories\CustomerRepository;
use Hyn\Tenancy\Models\Customer;
use Hyn\Tenancy\Tests\Test;
use Illuminate\Contracts\Foundation\Application;

class CustomerRepositoryTest extends Test
{
    /**
     * @var CustomerRepository
     */
    protected $customers;

    protected function duringSetUp(Application $app)
    {
        $this->customers = $app->make(CustomerRepository::class);
    }

    /**
     * @test
     */
    public function creation_succeeds()
    {
        $customer = new Customer();

        $customer->name = 'John Doe';
        $customer->email = 'john@doe.example';

        $customer = $this->customers->create($customer);

        $this->assertTrue($customer->exists);
    }
}
