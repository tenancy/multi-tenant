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

namespace Hyn\Tenancy\Tests\Extend;

use Hyn\Tenancy\Contracts\Website;
use Hyn\Tenancy\Events\Websites\Created;
use Hyn\Tenancy\Events\Websites\Deleted;
use Hyn\Tenancy\Events\Websites\Updated;
use Hyn\Tenancy\Contracts\Webserver\DatabaseGenerator;
use Hyn\Tenancy\Database\Connection;

class DatabaseDriverExtend implements DatabaseGenerator
{
    public function created(Created $event, array $config, Connection $connection): bool
    {
        return true;
    }

    public function updated(Updated $event, array $config, Connection $connection): bool
    {
        return true;
    }

    public function deleted(Deleted $event, array $config, Connection $connection): bool
    {
        return true;
    }

    public function updatePassword(Website $website, array $config, Connection $connection): bool
    {
        return true;
    }
}
