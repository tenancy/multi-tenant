<?php

namespace Hyn\Tenancy\Listeners\Database;

use Hyn\Tenancy\Abstracts\HostnameEvent;
use Hyn\Tenancy\Database\Connection;
use Illuminate\Contracts\Events\Dispatcher;
use Hyn\Tenancy\Events;

class ConnectsTenants
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(Events\Hostnames\Identified::class, [$this, 'switch']);
        $events->listen(Events\Hostnames\Switched::class, [$this, 'switch']);
        $events->listen(Events\Hostnames\Deleted::class, [$this, 'deleted']);
        $events->listen(Events\Hostnames\Created::class, [$this, 'migrate']);
    }

    /**
     * Acts on this service whenever a website is disabled.
     *
     * @param Events\Hostnames\Deleted $event
     * @return bool
     */
    public function deleted(Events\Hostnames\Deleted $event) : bool
    {
        if ($this->connection->current() && $this->connection->current()->id === $event->hostname->id) {
            $this->connection->purge();
        }

        return true;
    }

    /**
     * Reacts to this service when we switch the active tenant website.
     *
     * @param HostnameEvent $event
     * @return bool
     */
    public function switch(HostnameEvent $event) : bool
    {
        return $this->connection->set($event->hostname);
    }

    /**
     * @param HostnameEvent $event
     * @return bool
     */
    public function migrate(HostnameEvent $event): bool
    {
        if (config('tenancy.db.tenant-migrations-path')) {
            return $this->connection->migrate($event->hostname);
        }

        return true;
    }
}
