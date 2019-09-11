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

namespace Hyn\Tenancy\Generators\Webserver\Database;

use Hyn\Tenancy\Exceptions\GeneratorFailedException;
use Hyn\Tenancy\Contracts\Webserver\DatabaseGenerator;

class DatabaseDriverFactory
{
    public function create($driver = 'mysql') : DatabaseGenerator
    {
        $drivers = app('tenancy.db.drivers');

        if (!in_array($driver, $drivers->keys()->toArray())) {
            throw new GeneratorFailedException("Could not generate database for driver $driver");
        }

        return new $drivers[$driver]();
    }
}
