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

namespace Hyn\Tenancy\Traits;

use Hyn\Tenancy\Database\Connection;

trait TenantAwareConnection
{
    public function getConnectionName()
    {
        $hostname = app(\Hyn\Tenancy\Contracts\CurrentHostname::class);
    
        if ($hostname) {
            return app(Connection::class)->tenantName();
        } else {
            return app(Connection::class)->systemName();
        }
    }
}