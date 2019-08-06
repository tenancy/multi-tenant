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

namespace Hyn\Tenancy\Events\Database;

use Hyn\Tenancy\Abstracts\AbstractEvent;
use Hyn\Tenancy\Contracts\Website;

class ConnectionSet extends AbstractEvent
{
    /**
     * For which Website the connection was set.
     *
     * @var Website|null
     */
    public $website;
    /**
     * The connection name that was set up, tenant or system.
     *
     * @var string
     */
    public $connection;
    /**
     * The previous connection was disconnected and purged.
     *
     * @var bool
     */
    public $purged;

    public function __construct(Website $website = null, string $connection, bool $purged = true)
    {
        $this->website = $website;
        $this->connection = $connection;
        $this->purged = $purged;
    }
}
