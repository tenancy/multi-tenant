<?php

/*
 * This file is part of the hyn/multi-tenant package.
 *
 * (c) Daniël Klabbers <daniel@klabbers.email>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @see https://tenancy.dev
 * @see https://github.com/hyn/multi-tenant
 */

namespace Hyn\Tenancy\Facades;

use Hyn\Tenancy\Environment;
use Illuminate\Support\Facades\Facade;

class TenancyFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Environment::class;
    }
}
