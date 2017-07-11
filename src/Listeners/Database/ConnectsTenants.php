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
}
