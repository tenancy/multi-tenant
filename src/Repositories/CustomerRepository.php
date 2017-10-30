<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://laravel-tenancy.com
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Repositories;

use Hyn\Tenancy\Contracts\Repositories\CustomerRepository as Contract;
use Hyn\Tenancy\Events\Customers as Events;
use Hyn\Tenancy\Contracts\Customer;
use Hyn\Tenancy\Traits\DispatchesEvents;
use Illuminate\Database\Eloquent\Builder;

class CustomerRepository implements Contract
{
    use DispatchesEvents;
    /**
     * @var Customer
     */
    protected $customer;

    /**
     * WebsiteRepository constructor.
     * @param Customer $customer
     */
    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    /**
     * @param string $email
     * @return Customer|null
     */
    public function findByEmail(string $email)
    {
        return $this->customer->newQuery()->where('email', $email)->first();
    }

    /**
     * @param Customer $customer
     * @return Customer
     */
    public function create(Customer &$customer): Customer
    {
        if ($customer->exists) {
            return $this->update($customer);
        }

        $this->emitEvent(
            new Events\Creating($customer)
        );

        $customer->save();

        $this->emitEvent(
            new Events\Created($customer)
        );

        return $customer;
    }

    /**
     * @param Customer $customer
     * @return Customer
     */
    public function update(Customer &$customer): Customer
    {
        if (!$customer->exists) {
            return $this->create($customer);
        }

        $this->emitEvent(
            new Events\Updating($customer)
        );

        $customer->save();

        $this->emitEvent(
            new Events\Updated($customer)
        );

        return $customer;
    }

    /**
     * @param Customer $customer
     * @param bool $hard
     * @return Customer
     */
    public function delete(Customer &$customer, $hard = false): Customer
    {
        $this->emitEvent(
            new Events\Deleting($customer)
        );

        if ($hard) {
            $customer->forceDelete();
        } else {
            $customer->delete();
        }

        $this->emitEvent(
            new Events\Deleted($customer)
        );

        return $customer;
    }

    /**
     * @warn Only use for querying.
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->customer->newQuery();
    }
}
