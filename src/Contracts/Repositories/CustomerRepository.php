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

namespace Hyn\Tenancy\Contracts\Repositories;

use Hyn\Tenancy\Contracts\Customer;
use Illuminate\Database\Eloquent\Builder;

interface CustomerRepository
{
    /**
     * @param string $email
     * @return Customer|null
     */
    public function findByEmail(string $email);

    /**
     * @param Customer $customer
     * @return Customer
     */
    public function create(Customer &$customer): Customer;

    /**
     * @param Customer $customer
     * @return Customer
     */
    public function update(Customer &$customer): Customer;

    /**
     * @param Customer $customer
     * @param bool $hard
     * @return Customer
     */
    public function delete(Customer &$customer, $hard = false): Customer;

    /**
     * @warn Only use for querying.
     * @return Builder
     */
    public function query(): Builder;
}
