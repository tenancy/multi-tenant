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
 *
 */

namespace Hyn\Tenancy\Abstracts;

use Hyn\Tenancy\Models\Customer;

abstract class CustomerEvent extends AbstractEvent
{
    /**
     * @var Customer
     */
    public $customer;

    public function __construct(Customer &$customer)
    {
        $this->customer = &$customer;
    }
}
