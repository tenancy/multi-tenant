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

namespace Hyn\Tenancy\Listeners\Database;

use Hyn\Tenancy\Abstracts\WebsiteEvent;
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
        $events->listen(Events\Websites\Identified::class, [$this, 'switch']);
        $events->listen(Events\Websites\Switched::class, [$this, 'switch']);
        $events->listen(Events\Websites\Forgotten::class, [$this, 'forget']);
    }

    /**
     * Reacts to this service when we switch the active tenant website.
     *
     * @param WebsiteEvent $event
     * @return bool
     */
    public function switch(WebsiteEvent $event) : bool
    {
        return $this->connection->set($event->website);
    }

    /**
     * Reacts when the active tenant is released.
     *
     * purge() clears the stored configuration as well as the open connection,
     * which set(null) does not.
     *
     * @return bool
     */
    public function forget(): bool
    {
        $this->connection->purge();

        return true;
    }
}
