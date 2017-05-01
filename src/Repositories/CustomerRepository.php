<?php

namespace Hyn\Tenancy\Repositories;

use Hyn\Tenancy\Contracts\Repositories\CustomerRepository as Contract;
use Hyn\Tenancy\Events\Customers as Events;
use Hyn\Tenancy\Models\Customer;
use Hyn\Tenancy\Traits\DispatchesEvents;

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
    public function findByEmail(string $email): ?Customer
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
}
