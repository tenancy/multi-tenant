<?php

namespace Hyn\MultiTenant\Contracts;

use Hyn\Framework\Contracts\BaseRepositoryContract;

interface CustomerRepositoryContract extends BaseRepositoryContract
{
    /**
     * Load all customers.
     *
     * @return mixed
     */
    public function all();

    /**
     * Removes customer and everything related.
     *
     * @param $name
     *
     * @return bool|null
     */
    public function forceDeleteByName($name);

    /**
     * Find a customer by name.
     *
     * @param $name
     *
     * @return \Hyn\MultiTenant\Models\Customer
     */
    public function findByName($name);
}
