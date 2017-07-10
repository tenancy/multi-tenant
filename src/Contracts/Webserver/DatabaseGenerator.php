<?php

namespace Hyn\Tenancy\Contracts\Webserver;

use Hyn\Tenancy\Database\Connection;
use Hyn\Tenancy\Events\Websites\Created;
use Hyn\Tenancy\Events\Websites\Deleted;
use Hyn\Tenancy\Events\Websites\Updated;

interface DatabaseGenerator
{
    public function created(Created $event, array $config, Connection $connection): bool;
    public function updated(Updated $event, array $config, Connection $connection): bool;
    public function deleted(Deleted $event, array $config, Connection $connection): bool;
}
