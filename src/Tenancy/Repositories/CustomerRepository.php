<?php

namespace Hyn\MultiTenant\Repositories;

use Hyn\Framework\Repositories\BaseRepository;
use Hyn\MultiTenant\Contracts\CustomerRepositoryContract;

class CustomerRepository extends BaseRepository implements CustomerRepositoryContract
{
    /**
     * @var \Hyn\MultiTenant\Models\Customer
     */
    protected $customer;

    /**
     * Find a customer by name.
     *
     * @param $name
     *
     * @return \Hyn\MultiTenant\Models\Costumer
     */
    public function findByName($name)
    {
        return $this->customer->where('name', $name)->first();
    }

    /**
     * Removes customer and everything related.
     *
     * @param $name
     *
     * @return bool|null
     */
    public function forceDeleteByName($name)
    {
        $customer = $this->customer->where('name', $name)->first();

        return $customer ? $customer->delete() : null;
    }
}
