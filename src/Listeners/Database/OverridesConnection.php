<?php

namespace Hyn\Tenancy\Listeners\Database;

use Hyn\Tenancy\Database\Connection;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Str;

class OverridesConnection
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen('eloquent.booted:*', [$this, 'override']);
    }

    public function override(string $event, $model)
    {
        $class = Str::replaceFirst('eloquent.booted: ', null, $event);

        // Somehow on booting/booted the argument is an array.
        if (is_array($model)) {
            $model = array_first($model);
        }

        if (in_array($class, config('tenancy.db.force-tenant-connection-of-models', []))) {
            $model->setConnection($this->connection->tenantName());
        }

        if (in_array($class, config('tenancy.db.force-system-connection-of-models', []))) {
            $model->setConnection($this->connection->systemName());
        }
    }
}
