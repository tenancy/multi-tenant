<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://github.com/hyn/multi-tenant
 * @see https://hyn.me
 * @see https://patreon.com/tenancy
 */

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
     * @covers \Hyn\Tenancy\Repositories\CustomerRepository::create
     * @covers \Hyn\Tenancy\Contracts\Repositories\CustomerRepository::create
     */
    public function creation_succeeds()
    {
        $customer = new Customer();

        $customer->name = 'John Doe';
        $customer->email = 'john@doe.example';

        $customer = $this->customers->create($customer);

        $this->assertTrue($customer->exists);

        $this->assertTrue($this->customers->query()->where('id', $customer->id)->exists());
    }
}
