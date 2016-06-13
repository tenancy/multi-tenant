<?php

namespace Hyn\MultiTenant\Models;

/**
 * @deprecated Use Customer
 *
 * @info       the term tenant was quite confusing.
 */
class Tenant extends Customer
{
    protected $table = 'customers';
}
