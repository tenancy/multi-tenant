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

namespace Hyn\Tenancy\Tests\Commands;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Tests\Test;
use Hyn\Tenancy\Tests\Traits\InteractsWithMigrations;
use Illuminate\Contracts\Foundation\Application;

abstract class DatabaseCommandTest extends Test
{
    use InteractsWithMigrations;

    protected function duringSetUp(Application $app)
    {
        $this->setUpHostnames(true);
        $this->setUpWebsites(true, true);

        $this->connection = $app->make(Connection::class);
    }
}
